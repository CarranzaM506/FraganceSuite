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
        $product = Product::find($id);

        if (!$product) {
            return response()->json(['error' => 'Producto no encontrado'], 404);
        }

        // Obtener descuento si aplica
        $discount = 0;
        if ($product->discount) {
            $discount = $product->discount->value;
        }

        return response()->json([
            'id' => $product->idproduct,
            'name' => $product->name,
            'brand' => $product->brand,
            'category' => $product->category,
            'price' => $product->price,
            'image' => $product->pathimg,
            'discount' => $discount,
            'stock' => $product->stock,
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
            \Log::info('=== CART GET REQUEST ===');
            
            $user = Auth::user();
            if (!$user) {
                \Log::warning('User not authenticated');
                return response()->json(['error' => 'Unauthorized', 'items' => [], 'total' => 0], 401);
            }

            \Log::info('User authenticated', ['user_id' => $user->id]);

            $cart = Cart::where('iduser', $user->id)->first();
            
            if (!$cart) {
                \Log::info('No cart found for user', ['user_id' => $user->id]);
                return response()->json(['items' => [], 'total' => 0]);
            }

            \Log::info('Cart found', ['cart_id' => $cart->idcart]);

            $cartDetails = CartDetail::where('idcart', $cart->idcart)->get();
            \Log::info('CartDetails retrieved', ['count' => $cartDetails->count()]);

            $items = [];
            $total = 0;

            foreach ($cartDetails as $detail) {
                \Log::info('Processing CartDetail', ['detail_id' => $detail->idproduct]);
                
                $product = Product::find($detail->idproduct);
                
                if (!$product) {
                    \Log::warning('Product not found', ['product_id' => $detail->idproduct]);
                    continue;
                }

                $price = $product->price;
                $discount = $product->discount ? $product->discount->value : 0;
                $itemTotal = ($price - $discount) * $detail->quantity;
                $total += $itemTotal;

                $items[] = [
                    'id' => $product->idproduct,
                    'name' => $product->name,
                    'brand' => $product->brand ?? '',
                    'category' => $product->category ?? '',
                    'price' => floatval($price),
                    'image' => $product->pathimg,
                    'quantity' => intval($detail->quantity),
                    'discount' => floatval($discount),
                ];

                \Log::info('Item added to response', ['product_id' => $product->idproduct, 'quantity' => $detail->quantity]);
            }

            \Log::info('=== CART GET SUCCESS ===', ['items_count' => count($items), 'total' => $total]);
            return response()->json(['items' => $items, 'total' => $total]);
        } catch (\Exception $e) {
            \Log::error('=== CART GET ERROR ===', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            return response()->json(['error' => $e->getMessage(), 'items' => [], 'total' => 0], 500);
        }
    }

    public function add(Request $request)
    {
        try {
            \Log::info('=== CART ADD REQUEST ===', ['request' => $request->all()]);
            
            $user = Auth::user();
            if (!$user) {
                \Log::warning('User not authenticated');
                return response()->json(['error' => 'Unauthorized'], 401);
            }

            \Log::info('User authenticated', ['user_id' => $user->id]);

            $request->validate([
                'productId' => 'required|integer',
                'quantity' => 'integer|min:1',
            ]);

            $productId = $request->productId;
            $quantity = $request->quantity ?? 1;

            \Log::info('Validated data', ['productId' => $productId, 'quantity' => $quantity]);

            $product = Product::find($productId);
            if (!$product) {
                \Log::warning('Product not found', ['productId' => $productId]);
                return response()->json(['error' => 'Product not found'], 404);
            }

            \Log::info('Product found', ['productId' => $productId, 'product_name' => $product->name]);

            // Buscar o crear carrito (siguiendo el patrón del proyecto)
            $cart = Cart::where('iduser', $user->id)->first();
            if (!$cart) {
                \Log::info('Creating new cart for user', ['user_id' => $user->id]);
                $cart = new Cart();
                $cart->iduser = $user->id;
                $cart->save();
                \Log::info('Cart created', ['cart_id' => $cart->idcart]);
            } else {
                \Log::info('Cart found', ['cart_id' => $cart->idcart]);
            }

            // Buscar o crear detalle
            $cartDetail = CartDetail::where('idcart', $cart->idcart)
                ->where('idproduct', $productId)
                ->first();

            if ($cartDetail) {
                \Log::info('CartDetail found, updating quantity', ['old_qty' => $cartDetail->quantity]);
                $cartDetail->quantity += $quantity;
                $cartDetail->save();
                \Log::info('CartDetail updated', ['new_qty' => $cartDetail->quantity]);
            } else {
                \Log::info('Creating new CartDetail');
                $cartDetail = new CartDetail();
                $cartDetail->idcart = $cart->idcart;
                $cartDetail->idproduct = $productId;
                $cartDetail->quantity = $quantity;
                $cartDetail->save();
                \Log::info('CartDetail created', ['cart_id' => $cart->idcart, 'product_id' => $productId, 'quantity' => $quantity]);
            }

            \Log::info('=== CART ADD SUCCESS ===');
            return response()->json(['success' => true, 'cart_id' => $cart->idcart]);
            
        } catch (\Exception $e) {
            \Log::error('=== CART ADD ERROR ===', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

    public function update(Request $request)
    {
        try {
            \Log::info('=== CART UPDATE REQUEST ===', ['request' => $request->all()]);
            
            $user = Auth::user();
            if (!$user) {
                \Log::warning('User not authenticated');
                return response()->json(['error' => 'Unauthorized'], 401);
            }

            $request->validate([
                'productId' => 'required|integer',
                'quantity' => 'required|integer|min:0',
            ]);

            $productId = $request->productId;
            $quantity = $request->quantity;

            $cart = Cart::where('iduser', $user->id)->first();
            if (!$cart) {
                \Log::warning('Cart not found', ['user_id' => $user->id]);
                return response()->json(['error' => 'Cart not found'], 404);
            }

            if ($quantity <= 0) {
                CartDetail::where('idcart', $cart->idcart)->where('idproduct', $productId)->delete();
                \Log::info('CartDetail deleted', ['cart_id' => $cart->idcart, 'product_id' => $productId]);
            } else {
                $cartDetail = CartDetail::where('idcart', $cart->idcart)->where('idproduct', $productId)->first();

                if ($cartDetail) {
                    $cartDetail->quantity = $quantity;
                    $cartDetail->save();
                    \Log::info('CartDetail updated', ['quantity' => $quantity]);
                } else {
                    $cartDetail = new CartDetail();
                    $cartDetail->idcart = $cart->idcart;
                    $cartDetail->idproduct = $productId;
                    $cartDetail->quantity = $quantity;
                    $cartDetail->save();
                    \Log::info('CartDetail created', ['quantity' => $quantity]);
                }
            }

            \Log::info('=== CART UPDATE SUCCESS ===');
            return response()->json(['success' => true]);
        } catch (\Exception $e) {
            \Log::error('=== CART UPDATE ERROR ===', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

    public function remove(Request $request)
    {
        try {
            \Log::info('=== CART REMOVE REQUEST ===', ['request' => $request->all()]);
            
            $user = Auth::user();
            if (!$user) {
                \Log::warning('User not authenticated');
                return response()->json(['error' => 'Unauthorized'], 401);
            }

            $request->validate([
                'productId' => 'required|integer',
            ]);

            $productId = $request->productId;

            $cart = Cart::where('iduser', $user->id)->first();
            if (!$cart) {
                \Log::warning('Cart not found', ['user_id' => $user->id]);
                return response()->json(['error' => 'Cart not found'], 404);
            }

            CartDetail::where('idcart', $cart->idcart)->where('idproduct', $productId)->delete();
            \Log::info('CartDetail deleted', ['cart_id' => $cart->idcart, 'product_id' => $productId]);

            \Log::info('=== CART REMOVE SUCCESS ===');
            return response()->json(['success' => true]);
        } catch (\Exception $e) {
            \Log::error('=== CART REMOVE ERROR ===', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }
}

