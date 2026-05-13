<?php

namespace App\Http\Controllers;

use App\Models\Product;
use App\Models\Hero; 
use App\Models\Discount;
use App\Models\Brand;
use Illuminate\Support\Facades\DB;
use Illuminate\Http\Request;

class MainPageController extends Controller
{
    public function index()
    {
        // OBTENER HERO ACTIVO - SOLO 1 IMAGEN
        $heroImage = Hero::where('active', true)->first();

        if (!$heroImage) {
            $product = Product::where('active', true)
                ->where('decant', true)
                ->inRandomOrder()
                ->first();
                
            $heroImage = (object)[
                'image' => $product ? str_replace('public/storage/', '', $product->pathimg) : null,
                'title' => 'AROMA'
            ];
        }

        // Obtener marcas que tienen logo
        $brandsWithLogo = Brand::where('active', true)
            ->whereNotNull('logo')
            ->where('logo', '!=', '')
            ->orderBy('order')
            ->orderBy('brand_name')
            ->get();

        $activeBrands = $brandsWithLogo->count() >= 20 ? $brandsWithLogo : collect();
    
        // Obtener productos para MUJER
        $productsForWomen = Product::where(function($query) {
                $query->where('category', 'like', '%mujer%')
                    ->orWhere('category', 'like', '%Hombre|Mujer%');
            })
            ->where('active', true)
            ->inRandomOrder()
            ->limit(4)
            ->get();

        // Obtener productos para HOMBRE
        $productsForMen = Product::where(function($query) {
                $query->where('category', 'like', '%hombre%')
                    ->orWhere('category', 'like', '%Hombre|Mujer%');
            })
            ->where('active', true)
            ->inRandomOrder()
            ->limit(4)
            ->get();

        // ========== MÁS VENDIDOS DEL MES ==========
        $bestSellers = collect();

        try {
            $bestSellers = Product::select(
                    'product.idproduct',
                    'product.name',
                    'product.price',
                    'product.brand',
                    'product.pathimg',
                    DB::raw('SUM(orderdetail.quantity) as total_sold')
                )
                ->join('orderdetail', 'product.idproduct', '=', 'orderdetail.idproduct')
                ->join('order', 'orderdetail.idorder', '=', 'order.idorder')
                ->whereMonth('order.date', now()->month)
                ->whereYear('order.date', now()->year)
                ->groupBy(
                    'product.idproduct',
                    'product.name',
                    'product.price',
                    'product.brand',
                    'product.pathimg'
                )
                ->orderByDesc('total_sold')
                ->limit(2)
                ->get();
                
        } catch (\Exception $e) {
            \Log::error('Error en más vendidos: ' . $e->getMessage());
        }

        // Obtener promoción activa
        $activePromotion = Discount::where('startdate', '<=', now())
            ->where('enddate', '>=', now())
            ->orderBy('enddate', 'asc')
            ->first();

        $promotionProduct = null;
        if ($activePromotion) {
            $promotionProduct = Product::where('iddiscount', $activePromotion->iddiscount)
                ->where('active', true)
                ->inRandomOrder()
                ->first();
            
            if (!$promotionProduct) {
                $activePromotion = null;
            }
        }

        return view('mainPage.index', compact(
            'heroImage', 
            'productsForWomen',
            'productsForMen',
            'activePromotion',
            'promotionProduct',
            'activeBrands',
            'bestSellers'  
        ));
    }
}