<?php

namespace App\Http\Controllers;

use App\Models\Product;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

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
    public function getProductData($id): JsonResponse
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
    public function getCartPreview(Request $request): JsonResponse
    {
        // Los datos del carrito vienen del localStorage en el cliente (JavaScript)
        // Este endpoint sirve para cualquier lógica del servidor si es necesaria
        // Por ahora devolvemos un mensaje de éxito simple
        return response()->json([
            'status' => 'success',
            'message' => 'Preview cargado'
        ]);
    }
}

