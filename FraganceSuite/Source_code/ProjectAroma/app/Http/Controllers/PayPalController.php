<?php

namespace App\Http\Controllers;

use App\Models\Cart;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Srmklive\PayPal\Services\PayPal;

class PayPalController extends Controller
{
    public function createOrder(Request $request)
    {
        $order = null;

        try {
            $cart = Cart::where('iduser', Auth::id())->first();

            if (!$cart) {
                return response()->json(['error' => 'Carrito no encontrado'], 400);
            }

            $cartItems = \DB::table('cartdetail')
                ->join('product', 'cartdetail.idproduct', '=', 'product.idproduct')
                ->where('cartdetail.idcart', $cart->idcart)
                ->select('product.price', 'cartdetail.quantity')
                ->get();

            if ($cartItems->isEmpty()) {
                return response()->json(['error' => 'Carrito vacío'], 400);
            }

            $subtotal = 0;
            $totalDiscount = 0;

            foreach ($cartItems as $item) {
                $price = $item->price;
                $qty = $item->quantity;
                $discount = $item->discount ?? 0;

                $subtotal += $price * $qty;
                $totalDiscount += ($price * $qty) * ($discount / 100);
            }

            $totalAfterProductDiscount = $subtotal - $totalDiscount;

            $promoValue = session('promo_value', 0);
            $codeDiscount = $totalAfterProductDiscount * ($promoValue / 100);

            $finalTotal = $totalAfterProductDiscount - $codeDiscount;

            if ($finalTotal <= 0) {
                return response()->json([
                    'error' => 'Total inválido',
                    'total' => $finalTotal
                ], 400);
            }

            $finalTotal = number_format($finalTotal, 2, '.', '');

            $provider = new PayPal();
            $provider->setApiCredentials(config('paypal'));
            $provider->getAccessToken();

            $exchangeRate = 520; // aprox CRC → USD

            $finalTotalUSD = $finalTotal / $exchangeRate;
            $finalTotalUSD = number_format($finalTotalUSD, 2, '.', '');

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
                return response()->json([
                    'error' => 'PayPal no devolvió ID',
                    'paypal_response' => $order
                ], 500);
            }

            return response()->json([
                'id' => $order['id']
            ]);
        } catch (\Exception $e) {

            return response()->json([
                'error' => $e->getMessage(),
                'line' => $e->getLine()
            ], 500);
        }
    }

    public function captureOrder(Request $request)
    {

        $provider = new \Srmklive\PayPal\Services\PayPal;
        $provider->setApiCredentials(config('paypal'));
        $provider->getAccessToken();

        $response = $provider->capturePaymentOrder($request->orderID);

        return response()->json($response);
    }
}
