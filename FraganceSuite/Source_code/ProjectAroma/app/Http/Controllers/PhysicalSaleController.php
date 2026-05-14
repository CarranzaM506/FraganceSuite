<?php

namespace App\Http\Controllers;

use App\Models\Order;
use App\Models\OrderDetail;
use App\Models\Product;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class PhysicalSaleController extends Controller
{
    public function store(Request $request)
    {
        $validated = $request->validate([
            'items'          => 'required|array|min:1',
            'items.*.idproduct' => 'required|integer|exists:product,idproduct',
            'items.*.qty'    => 'required|integer|min:1',
            'items.*.price'  => 'required|numeric|min:0',
            'payment_method' => 'required|in:efectivo,sinpe,transferencia',
            'total'          => 'required|numeric|min:0',
        ]);

        try {
            $order = DB::transaction(function () use ($validated) {
                $order = Order::create([
                    'date'          => now(),
                    'state'         => 1,
                    'total'         => $validated['total'],
                    'purchasemethod'=> strtoupper($validated['payment_method']),
                    'sale_type'     => 'physical',
                    'guidenumber'   => null,
                    'iduser'        => Auth::id(),
                    'idlocation'    => null,
                ]);

                foreach ($validated['items'] as $item) {
                    $product = Product::where('idproduct', $item['idproduct'])
                        ->lockForUpdate()
                        ->first();

                    if (!$product) {
                        throw new \RuntimeException('Producto no encontrado: ' . $item['idproduct']);
                    }

                    if ((int) $product->stock < (int) $item['qty']) {
                        throw new \RuntimeException('Stock insuficiente para: ' . $product->name);
                    }

                    OrderDetail::create([
                        'idorder'   => $order->idorder,
                        'idproduct' => $item['idproduct'],
                        'quantity'  => $item['qty'],
                        'price'     => $item['price'],
                    ]);

                    $product->decrement('stock', $item['qty']);
                }

                return $order;
            });

            return response()->json(['success' => true, 'order_id' => $order->idorder]);

        } catch (\RuntimeException $e) {
            return response()->json(['success' => false, 'error' => $e->getMessage()], 422);
        } catch (\Throwable $e) {
            Log::error('PhysicalSale store error', ['message' => $e->getMessage()]);
            return response()->json(['success' => false, 'error' => 'Error interno. Intenta nuevamente.'], 500);
        }
    }
}
