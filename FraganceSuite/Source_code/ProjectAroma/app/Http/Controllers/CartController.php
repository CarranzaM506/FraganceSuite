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
        $user = Auth::user();
        if (!$user) {
            return response()->json(['error' => 'Unauthorized'], 401);
        }

        $cart = Cart::where('iduser', $user->id)->with('cartDetails.product')->first();

        if (!$cart) {
            return response()->json(['items' => [], 'total' => 0]);
        }

        $items = [];
        $total = 0;

        foreach ($cart->cartDetails as $detail) {
            $product = $detail->product;
            $price = $product->price;
            $discount = $product->discount ? $product->discount->value : 0;
            $itemTotal = ($price - $discount) * $detail->quantity;
            $total += $itemTotal;

            $items[] = [
                'id' => $product->idproduct,
                'name' => $product->name,
                'brand' => $product->brand,
                'category' => $product->category,
                'price' => $price,
                'image' => $product->pathimg,
                'quantity' => $detail->quantity,
                'discount' => $discount,
            ];
        }

        return response()->json(['items' => $items, 'total' => $total]);
    }

    public function add(Request $request)
    {
        $user = Auth::user();
        if (!$user) {
            return response()->json(['error' => 'Unauthorized'], 401);
        }

        $request->validate([
            'productId' => 'required|integer',
            'quantity' => 'integer|min:1',
        ]);

        $productId = $request->productId;
        $quantity = $request->quantity ?? 1;

        $product = Product::find($productId);
        if (!$product) {
            return response()->json(['error' => 'Product not found'], 404);
        }

        $cart = Cart::firstOrCreate(['iduser' => $user->id]);

        $cartDetail = CartDetail::updateOrCreate(
            ['idcart' => $cart->idcart, 'idproduct' => $productId],
            ['quantity' => \DB::raw('quantity + ' . $quantity)]
        );

        return response()->json(['success' => true]);
    }

    public function update(Request $request)
    {
        $user = Auth::user();
        if (!$user) {
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
            return response()->json(['error' => 'Cart not found'], 404);
        }

        if ($quantity <= 0) {
            CartDetail::where('idcart', $cart->idcart)->where('idproduct', $productId)->delete();
        } else {
            CartDetail::updateOrCreate(
                ['idcart' => $cart->idcart, 'idproduct' => $productId],
                ['quantity' => $quantity]
            );
        }

        return response()->json(['success' => true]);
    }

    public function remove(Request $request)
    {
        $user = Auth::user();
        if (!$user) {
            return response()->json(['error' => 'Unauthorized'], 401);
        }

        $request->validate([
            'productId' => 'required|integer',
        ]);

        $productId = $request->productId;

        $cart = Cart::where('iduser', $user->id)->first();
        if (!$cart) {
            return response()->json(['error' => 'Cart not found'], 404);
        }

        CartDetail::where('idcart', $cart->idcart)->where('idproduct', $productId)->delete();

        return response()->json(['success' => true]);
    }
}

