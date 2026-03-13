<?php

namespace App\Http\Controllers;

use App\Models\Product;
use Illuminate\Http\Request;

class ProductDetailController extends Controller
{
    public function index()
    {
        $products = Product::all();
        return view('dashboard.product.index', compact('products'));
    }

    /**
     * Display the specified product.
     */
    public function show(string $id)
    {
        // Cargar el producto con su descuento asociado si existe
        $product = Product::with('discount')->findOrFail($id);
        
        // Calcular precio con descuento si aplica
        $discountedPrice = null;
        if ($product->discount && $product->discount->active) {
            $discountValue = $product->discount->value;
            $discountedPrice = $product->price * (1 - ($discountValue / 100));
        }
        
        // Obtener productos relacionados (misma marca o categoría, excluyendo el actual)
        $relatedProducts = Product::where('active', true)
            ->where('idproduct', '!=', $id)
            ->where(function($query) use ($product) {
                $query->where('brand', $product->brand)
                      ->orWhere('category', $product->category);
            })
            ->limit(4)
            ->get();
        
        
        return view('productDetail.index', compact('product', 'discountedPrice', 'relatedProducts'));   
         }
}