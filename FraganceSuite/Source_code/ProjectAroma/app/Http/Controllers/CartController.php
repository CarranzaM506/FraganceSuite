<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\Cart;
use App\Models\CartDetail;
use App\Models\Product;

class CartController extends Controller
{
    public function index()
    {
        // Obtener todos los productos para datos que pudiera necesitar
        $products = Product::all();

        return view('cart.index', [
            'products' => $products
        ]);
    }

    /**
     * Obtener datos del producto en formato JSON
     * Incluye descuento si aplica
     */
    public function getProductData($id)
    {
        $product = Product::with('discount')->find($id);

        if (!$product) {
            return response()->json(['error' => 'Producto no encontrado'], 404);
        }

        $discount = $product->discount ? floatval($product->discount->value) : 0;

        return response()->json([
            'id' => (int)$product->idproduct,
            'name' => $product->name,
            'brand' => $product->brand,
            'category' => $product->category,
            'price' => floatval($product->price),
            'image' => $product->pathimg,
            'discount' => $discount,
            'stock' => (int)$product->stock,
        ]);
    }

    /**
     * Obtener preview del carrito con los productos actuales
     * Devuelve HTML con los productos en miniatura
     */
    public function getCartPreview(Request $request)
    {
        // Los datos del carrito vienen del localStorage en el cliente (JavaScript)
        // Este endpoint sirve para cualquier lógica del servidor si es necesaria
        // Por ahora devolvemos un mensaje de éxito simple
        return response()->json([
            'status' => 'success',
            'message' => 'Preview cargado'
        ]);
    }

    public function get()
    {
        try {
            $user = Auth::user();
            if (!$user) {
                return response()->json(['error' => 'Unauthorized', 'items' => [], 'total' => 0], 401);
            }

            // Obtener carrito del usuario
            $cart = Cart::where('iduser', $user->id)->first();
            
            if (!$cart) {
                return response()->json(['items' => [], 'total' => 0]);
            }

            // Cargar cartDetails con eager loading (relaciones precargadas)
            $cartDetails = CartDetail::where('idcart', $cart->idcart)
                ->with('product.discount')
                ->get();

            $items = [];
            $total = 0;

            foreach ($cartDetails as $detail) {
                if (!$detail->product) {
                    continue;
                }

                $price = floatval($detail->product->price);
                $discount = $detail->product->discount ? floatval($detail->product->discount->value) : 0;
                $itemTotal = ($price - $discount) * $detail->quantity;
                $total += $itemTotal;

                $items[] = [
                    'id' => (int)$detail->product->idproduct,
                    'name' => $detail->product->name,
                    'brand' => $detail->product->brand ?? '',
                    'category' => $detail->product->category ?? '',
                    'price' => $price,
                    'image' => $detail->product->pathimg,
                    'quantity' => (int)$detail->quantity,
                    'discount' => $discount,
                ];
            }

            return response()->json(['items' => $items, 'total' => $total]);
        } catch (\Exception $e) {
            \Log::error('CART GET ERROR', ['error' => $e->getMessage()]);
            return response()->json(['error' => $e->getMessage(), 'items' => [], 'total' => 0], 500);
        }
    }

    public function add(Request $request)
    {
        try {
            $user = Auth::user();
            if (!$user) {
                return response()->json(['error' => 'Unauthorized'], 401);
            }

            $request->validate([
                'productId' => 'required|integer',
                'quantity' => 'integer|min:1',
            ]);

            $productId = intval($request->productId);
            $quantity = intval($request->quantity ?? 1);

            // Obtener o crear carrito
            $cart = Cart::firstOrCreate(
                ['iduser' => $user->id],
                ['created_at' => now(), 'updated_at' => now()]
            );

            // Buscar detalle del carrito
            $cartDetail = CartDetail::where('idcart', $cart->idcart)
                ->where('idproduct', $productId)
                ->first();

            if ($cartDetail) {
                // Si existe, sumar cantidad
                $cartDetail->increment('quantity', $quantity);
            } else {
                // Si no existe, crear
                CartDetail::create([
                    'idcart' => $cart->idcart,
                    'idproduct' => $productId,
                    'quantity' => $quantity
                ]);
            }

            return response()->json(['success' => true, 'cart_id' => $cart->idcart]);
        } catch (\Exception $e) {
            \Log::error('CART ADD ERROR', ['error' => $e->getMessage()]);
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

    public function update(Request $request)
    {
        try {
            $user = Auth::user();
            if (!$user) {
                return response()->json(['error' => 'Unauthorized'], 401);
            }

            $request->validate([
                'productId' => 'required|integer',
                'quantity' => 'required|integer|min:0',
            ]);

            $productId = intval($request->productId);
            $quantity = intval($request->quantity);

            $cart = Cart::where('iduser', $user->id)->first();
            if (!$cart) {
                return response()->json(['error' => 'Cart not found'], 404);
            }

            if ($quantity <= 0) {
                // Eliminar si cantidad es 0 o negativa
                CartDetail::where('idcart', $cart->idcart)
                    ->where('idproduct', $productId)
                    ->delete();
            } else {
                // Actualizar cantidad
                CartDetail::where('idcart', $cart->idcart)
                    ->where('idproduct', $productId)
                    ->update(['quantity' => $quantity]);
            }

            return response()->json(['success' => true]);
        } catch (\Exception $e) {
            \Log::error('CART UPDATE ERROR', ['error' => $e->getMessage()]);
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

    public function remove(Request $request)
    {
        try {
            $user = Auth::user();
            if (!$user) {
                return response()->json(['error' => 'Unauthorized'], 401);
            }

            $request->validate([
                'productId' => 'required|integer',
            ]);

            $productId = intval($request->productId);

            $cart = Cart::where('iduser', $user->id)->first();
            if (!$cart) {
                return response()->json(['error' => 'Cart not found'], 404);
            }

            // Eliminar en una sola query
            CartDetail::where('idcart', $cart->idcart)
                ->where('idproduct', $productId)
                ->delete();

            return response()->json(['success' => true]);
        } catch (\Exception $e) {
            \Log::error('CART REMOVE ERROR', ['error' => $e->getMessage()]);
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }
}

