<?php

namespace App\Http\Controllers;

use App\Models\Cart;
use App\Models\CartDetail;
use App\Models\Order;
use App\Models\OrderDetail;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Srmklive\PayPal\Services\PayPal;

class PayPalController extends Controller
{
    public function createOrder(Request $request)
    {
        try {

            Log::info('SESSION:', session()->all());

            $cart = Cart::where('iduser', Auth::id())->first();

            if (!$cart) {
                return response()->json(['error' => 'Carrito no encontrado'], 400);
            }

            $cartItems = $cart->cartDetails()->with('product')->get();

            if ($cartItems->isEmpty()) {
                return response()->json(['error' => 'Carrito vacío'], 400);
            }

            $subtotal = 0;
            $totalDiscount = 0;

            foreach ($cartItems as $item) {

                if (!$item->product) {
                    Log::warning('Producto no encontrado en cartDetail', [
                        'cartDetail_id' => $item->id
                    ]);
                    continue;
                }

                $price = (float) $item->product->price;
                $qty   = (int) $item->quantity;

                // 🔹 Si luego manejas descuentos por producto, aquí
                $discount = 0;

                $subtotal += $price * $qty;
                $totalDiscount += ($price * $qty) * ($discount / 100);
            }

            $totalAfterProductDiscount = $subtotal - $totalDiscount;

            $promoValue = (float) session('promo_value', 0);
            $codeDiscount = $totalAfterProductDiscount * ($promoValue / 100);

            $finalTotal = $totalAfterProductDiscount - $codeDiscount;

            Log::info('TOTAL DEBUG', [
                'subtotal' => $subtotal,
                'totalDiscount' => $totalDiscount,
                'promoValue' => $promoValue,
                'codeDiscount' => $codeDiscount,
                'finalTotal' => $finalTotal
            ]);

            if ($finalTotal <= 0) {
                return response()->json([
                    'error' => 'Total inválido',
                    'debug' => [
                        'subtotal' => $subtotal,
                        'finalTotal' => $finalTotal
                    ]
                ], 400);
            }

            $finalTotal = number_format($finalTotal, 2, '.', '');

            $exchangeRate = 520;
            $finalTotalUSD = (float) $finalTotal / $exchangeRate;
            $finalTotalUSD = number_format($finalTotalUSD, 2, '.', '');

            if ($finalTotalUSD <= 0) {
                return response()->json([
                    'error' => 'Total en USD inválido',
                    'usd' => $finalTotalUSD
                ], 400);
            }

            $provider = new PayPal();
            $provider->setApiCredentials(config('paypal'));
            $provider->getAccessToken();

            $order = $provider->createOrder([
                "intent" => "CAPTURE",
                "purchase_units" => [[
                    "amount" => [
                        "currency_code" => "USD",
                        "value" => $finalTotalUSD
                    ]
                ]]
            ]);

            if (!$order || !isset($order['id'])) {
                Log::error('PayPal error', ['response' => $order]);

                return response()->json([
                    'error' => 'PayPal no devolvió ID',
                    'paypal_response' => $order
                ], 500);
            }

            return response()->json([
                'id' => $order['id']
            ]);
        } catch (\Exception $e) {

            Log::error('ERROR createOrder', [
                'message' => $e->getMessage(),
                'line' => $e->getLine()
            ]);

            return response()->json([
                'error' => $e->getMessage(),
                'line' => $e->getLine()
            ], 500);
        }
    }

    public function captureOrder(Request $request)
    {
        try {

            Log::info('CAPTURE REQUEST', $request->all());

            if (!$request->orderID) {
                return response()->json([
                    'error' => 'No orderID'
                ], 400);
            }

            $provider = new \Srmklive\PayPal\Services\PayPal;
            $provider->setApiCredentials(config('paypal'));
            $provider->getAccessToken();

            $response = $provider->capturePaymentOrder($request->orderID);

            Log::info('PAYPAL RESPONSE', $response);

            if (!isset($response['status']) || $response['status'] !== 'COMPLETED') {
                return response()->json([
                    'error' => 'Pago no completado',
                    'paypal' => $response
                ], 400);
            }

            if (!$request->address_id) {
                return response()->json([
                    'error' => 'No address_id'
                ], 400);
            }

            $order = DB::transaction(function () use ($request, $response) {

                $cart = Cart::where('iduser', Auth::id())->first();

                if (!$cart) {
                    throw new \Exception('Carrito no encontrado');
                }

                $cartItems = $cart->cartDetails()->get();

                if ($cartItems->isEmpty()) {
                    throw new \Exception('Carrito vacío');
                }

                $order = Order::create([
                    'date' => today(),
                    'state' => 1,
                    'total' => $response['purchase_units'][0]['payments']['captures'][0]['amount']['value'],
                    'purchasemethod' => 'PAYPAL',
                    'guidenumber' => $response['id'],
                    'iduser' => Auth::id(),
                    'idlocation' => $request->address_id
                ]);

                foreach ($cartItems as $item) {

                    if (!$item->product) {
                        throw new \Exception('Producto no encontrado');
                    }

                    if ($item->product->stock < $item->quantity) {
                        throw new \Exception('Stock insuficiente para el producto ID ' . $item->idproduct);
                    }

                    OrderDetail::create([
                        'idorder' => $order->idorder,
                        'idproduct' => $item->idproduct,
                        'quantity' => $item->quantity,
                        'price' => $item->product->price
                    ]);

                    $item->product->decrement('stock', $item->quantity);
                }

                session()->forget(['promo_code', 'promo_value']);

                $cart->cartDetails()->delete();
                return $order;
            });

            return response()->json([
                'success' => true,
                'order_id' => $order->idorder
            ]);
        } catch (\Exception $e) {

            Log::error('CAPTURE ERROR', [
                'message' => $e->getMessage(),
                'line' => $e->getLine()
            ]);

            return response()->json([
                'error' => $e->getMessage()
            ], 500);
        }
    }
}
