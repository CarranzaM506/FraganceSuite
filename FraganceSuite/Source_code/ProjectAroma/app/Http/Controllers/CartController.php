<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Schema;
use App\Models\Cart;
use App\Models\CartDetail;
use App\Models\Product;
use App\Models\CodePromotion;

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
                $itemTotal = $price * (1 - ($discount / 100)) * $detail->quantity;
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
                    'stock' => (int)$detail->product->stock,
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

            $product = Product::find($productId);
            if (!$product) {
                return response()->json(['error' => 'Producto no encontrado'], 404);
            }

            $stock = intval($product->stock ?? 0);
            if ($stock < 1) {
                return response()->json(['error' => 'No hay unidades disponibles'], 422);
            }

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
                $newQuantity = intval($cartDetail->quantity) + $quantity;
                if ($newQuantity > $stock) {
                    return response()->json([
                        'error' => 'No hay suficientes unidades disponibles',
                        'available' => $stock
                    ], 422);
                }
                // Si existe, sumar cantidad
                $cartDetail->increment('quantity', $quantity);
            } else {
                if ($quantity > $stock) {
                    return response()->json([
                        'error' => 'No hay suficientes unidades disponibles',
                        'available' => $stock
                    ], 422);
                }
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

            $product = Product::find($productId);
            if (!$product) {
                return response()->json(['error' => 'Producto no encontrado'], 404);
            }

            $stock = intval($product->stock ?? 0);
            if ($quantity > $stock) {
                return response()->json([
                    'error' => 'No hay suficientes unidades disponibles',
                    'available' => $stock
                ], 422);
            }

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

    public function applyDiscountCode(Request $request)
    {
        try {
            $user = Auth::user();
            if (!$user) {
                return response()->json(['error' => 'Unauthorized'], 401);
            }

            $request->validate([
                'code' => 'required|string|max:255',
            ]);

            $code = trim($request->code);
            if ($code === '') {
                return response()->json(['error' => 'Código inválido'], 422);
            }

            $query = CodePromotion::query()
                ->whereRaw('LOWER(code_promotion) = ?', [strtolower($code)]);

            $now = now()->toDateString();
            if (Schema::hasColumn('code_promotion', 'startdate')) {
                $query->whereDate('startdate', '<=', $now);
            } elseif (Schema::hasColumn('code_promotion', 'start_date')) {
                $query->whereDate('start_date', '<=', $now);
            }
            if (Schema::hasColumn('code_promotion', 'enddate')) {
                $query->whereDate('enddate', '>=', $now);
            } elseif (Schema::hasColumn('code_promotion', 'end_date')) {
                $query->whereDate('end_date', '>=', $now);
            }

            $promotion = $query->first();
            if (!$promotion) {
                return response()->json(['error' => 'Código inválido'], 404);
            }

            return response()->json([
                'valid' => true,
                'code' => $promotion->code_promotion,
                'value' => floatval($promotion->value)
            ]);
        } catch (\Exception $e) {
            \Log::error('CART APPLY CODE ERROR', ['error' => $e->getMessage()]);
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }
}
