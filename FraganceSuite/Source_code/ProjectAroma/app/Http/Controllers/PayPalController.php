<?php

namespace App\Http\Controllers;

use App\Models\Cart;
use App\Models\CartDetail;
use App\Models\Location;
use App\Models\Order;
use App\Models\OrderDetail;
use App\Models\Product;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\Rule;
use Srmklive\PayPal\Services\PayPal;

class PayPalController extends Controller
{
    private const EXCHANGE_RATE_CRC_TO_USD = 520;

    public function createOrder(Request $request)
    {
        $validated = $request->validate([
            'address_id' => [
                'required',
                'integer',
                Rule::exists('location', 'idlocation')->where(function ($query) {
                    $query->where('iduser', Auth::id());
                }),
            ],
        ]);

        try {
            $cart = Cart::where('iduser', Auth::id())->first();

            if (!$cart) {
                return response()->json(['error' => 'Carrito no encontrado'], 400);
            }

            $cartItems = $cart->cartDetails()->with('product.discount')->get();

            if ($cartItems->isEmpty()) {
                return response()->json(['error' => 'Carrito vacio'], 400);
            }

            $totals = $this->calculateCartTotals($cartItems);
            $finalTotal = $totals['final_total_crc'];

            if ($finalTotal <= 0) {
                return response()->json([
                    'error' => 'Total invalido',
                ], 400);
            }

            $finalTotalUSD = $finalTotal / self::EXCHANGE_RATE_CRC_TO_USD;
            $finalTotalUSD = number_format($finalTotalUSD, 2, '.', '');

            if ((float) $finalTotalUSD <= 0) {
                return response()->json([
                    'error' => 'Total en USD invalido',
                ], 400);
            }

            $order = $this->withPaymentProvider(function (PayPal $provider) use ($finalTotalUSD) {
                return $provider->createOrder([
                    'intent' => 'CAPTURE',
                    'purchase_units' => [[
                        'amount' => [
                            'currency_code' => 'USD',
                            'value' => $finalTotalUSD,
                        ],
                    ]],
                ]);
            });

            if (!$order || !isset($order['id'])) {
                Log::error('PayPal createOrder response invalida', [
                    'user_id' => Auth::id(),
                    'address_id' => $validated['address_id'],
                    'paypal_response' => $order,
                ]);

                return response()->json([
                    'error' => 'No fue posible iniciar el pago. Intenta nuevamente.',
                ], 502);
            }

            return response()->json([
                'id' => $order['id'],
            ]);
        } catch (\Throwable $e) {
            Log::error('ERROR createOrder', [
                'message' => $e->getMessage(),
                'line' => $e->getLine(),
                'user_id' => Auth::id(),
            ]);

            return response()->json([
                'error' => 'No fue posible iniciar el pago por un problema temporal con la pasarela. Intenta nuevamente.',
                'retryable' => true,
                'code' => 'PAYMENT_GATEWAY_UNAVAILABLE',
            ], 503);
        }
    }

    public function captureOrder(Request $request)
    {
        $validated = $request->validate([
            'orderID' => ['required', 'string', 'max:100'],
            'address_id' => [
                'required',
                'integer',
                Rule::exists('location', 'idlocation')->where(function ($query) {
                    $query->where('iduser', Auth::id());
                }),
            ],
        ]);

        try {
            Log::info('CAPTURE REQUEST', [
                'order_id' => $validated['orderID'],
                'address_id' => $validated['address_id'],
                'user_id' => Auth::id(),
            ]);

            $existingOrder = Order::where('guidenumber', $validated['orderID'])
                ->where('iduser', Auth::id())
                ->first();

            if ($existingOrder) {
                return response()->json([
                    'success' => true,
                    'order_id' => $existingOrder->idorder,
                    'already_processed' => true,
                ]);
            }

            $response = $this->withPaymentProvider(function (PayPal $provider) use ($validated) {
                return $provider->capturePaymentOrder($validated['orderID']);
            });

            Log::info('PAYPAL RESPONSE', [
                'user_id' => Auth::id(),
                'order_id' => $validated['orderID'],
                'status' => $response['status'] ?? null,
            ]);

            if (!isset($response['status']) || $response['status'] !== 'COMPLETED') {
                return response()->json([
                    'error' => $this->mapGatewayErrorMessage($response),
                    'retryable' => true,
                    'code' => 'PAYMENT_REJECTED',
                ], 422);
            }

            $captureAmount = $response['purchase_units'][0]['payments']['captures'][0]['amount']['value'] ?? null;
            if ($captureAmount === null) {
                Log::error('PayPal capture sin monto', [
                    'user_id' => Auth::id(),
                    'order_id' => $validated['orderID'],
                    'paypal_response' => $response,
                ]);

                return response()->json([
                    'error' => 'No se pudo confirmar el monto del pago. Intenta nuevamente.',
                    'retryable' => true,
                    'code' => 'PAYMENT_AMOUNT_MISSING',
                ], 502);
            }

            $paymentMethod = $this->resolvePaymentMethod($response);

            $order = DB::transaction(function () use ($validated, $response, $captureAmount, $paymentMethod) {
                $cart = Cart::where('iduser', Auth::id())
                    ->lockForUpdate()
                    ->first();

                if (!$cart) {
                    throw new \RuntimeException('Carrito no encontrado');
                }

                $cartItems = CartDetail::where('idcart', $cart->idcart)
                    ->lockForUpdate()
                    ->get();

                if ($cartItems->isEmpty()) {
                    throw new \RuntimeException('Carrito vacio');
                }

                $addressExists = Location::where('idlocation', $validated['address_id'])
                    ->where('iduser', Auth::id())
                    ->exists();

                if (!$addressExists) {
                    throw new \RuntimeException('Direccion invalida para este usuario');
                }

                $order = Order::create([
                    'date' => today(),
                    'state' => 1,
                    'total' => $captureAmount,
                    'purchasemethod' => $paymentMethod,
                    'guidenumber' => $response['id'],
                    'iduser' => Auth::id(),
                    'idlocation' => $validated['address_id'],
                ]);

                foreach ($cartItems as $item) {
                    $product = Product::where('idproduct', $item->idproduct)
                        ->lockForUpdate()
                        ->first();

                    if (!$product) {
                        throw new \RuntimeException('Producto no encontrado');
                    }

                    if ((int) $product->stock < (int) $item->quantity) {
                        throw new \RuntimeException('Stock insuficiente para el producto ID ' . $item->idproduct);
                    }

                    OrderDetail::create([
                        'idorder' => $order->idorder,
                        'idproduct' => $item->idproduct,
                        'quantity' => $item->quantity,
                        'price' => $product->price,
                    ]);

                    $product->decrement('stock', $item->quantity);
                }

                session()->forget(['promo_code', 'promo_value']);
                $cart->cartDetails()->delete();

                return $order;
            }, 3);

            return response()->json([
                'success' => true,
                'order_id' => $order->idorder,
            ]);
        } catch (\RuntimeException $e) {
            Log::warning('CAPTURE RULE ERROR', [
                'message' => $e->getMessage(),
                'user_id' => Auth::id(),
            ]);

            return response()->json([
                'error' => $e->getMessage(),
                'retryable' => true,
                'code' => 'PAYMENT_BUSINESS_RULE',
            ], 422);
        } catch (\Throwable $e) {
            Log::error('CAPTURE ERROR', [
                'message' => $e->getMessage(),
                'line' => $e->getLine(),
                'user_id' => Auth::id(),
            ]);

            return response()->json([
                'error' => 'No se pudo completar el pago por un problema temporal con la pasarela. Intenta nuevamente.',
                'retryable' => true,
                'code' => 'PAYMENT_GATEWAY_UNAVAILABLE',
            ], 503);
        }
    }

    private function calculateCartTotals($cartItems): array
    {
        $subtotal = 0.0;
        $totalDiscount = 0.0;

        foreach ($cartItems as $item) {
            if (!$item->product) {
                Log::warning('Producto no encontrado en cartDetail', [
                    'idproduct' => $item->idproduct ?? null,
                    'idcart' => $item->idcart ?? null,
                    'user_id' => Auth::id(),
                ]);
                continue;
            }

            $price = (float) $item->product->price;
            $qty = (int) $item->quantity;
            $discount = (float) ($item->product->discount->value ?? 0);

            $subtotal += $price * $qty;
            $totalDiscount += ($price * $qty) * ($discount / 100);
        }

        $totalAfterProductDiscount = $subtotal - $totalDiscount;
        $promoValue = (float) session('promo_value', 0);
        $codeDiscount = $totalAfterProductDiscount * ($promoValue / 100);
        $finalTotal = $totalAfterProductDiscount - $codeDiscount;

        return [
            'subtotal_crc' => round($subtotal, 2),
            'discount_crc' => round($totalDiscount + $codeDiscount, 2),
            'final_total_crc' => round($finalTotal, 2),
        ];
    }

    private function withPaymentProvider(callable $callback)
    {
        $provider = new PayPal();
        $provider->setApiCredentials(config('paypal'));
        $provider->getAccessToken();

        return $callback($provider);
    }

    private function resolvePaymentMethod(array $response): string
    {
        if (!empty($response['payment_source']['card'])) {
            return 'CARD';
        }

        if (!empty($response['payment_source']['paypal'])) {
            return 'PAYPAL';
        }

        return 'PAYPAL';
    }

    private function mapGatewayErrorMessage(array $response): string
    {
        $details = $response['details'][0] ?? [];
        $issue = $details['issue'] ?? null;

        return match ($issue) {
            'INSTRUMENT_DECLINED' => 'El medio de pago fue rechazado. Intenta con otra tarjeta o cuenta.',
            'PAYER_CANNOT_PAY' => 'No se pudo procesar el pago con este medio. Intenta nuevamente.',
            'TRANSACTION_REFUSED' => 'La transaccion fue rechazada por la pasarela.',
            'CARD_EXPIRED' => 'La tarjeta esta vencida.',
            'INVALID_SECURITY_CODE' => 'El CVV ingresado no es valido.',
            default => 'El pago no fue aprobado. Verifica los datos e intenta nuevamente.',
        };
    }
}
