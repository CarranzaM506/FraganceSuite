<?php

namespace App\Http\Controllers;

use App\Models\Product;
use App\Models\Slider;
use App\Models\Discount;
use Illuminate\Support\Str;
use Illuminate\Http\Request;

class MainPageController extends Controller
{
    public function index()
    {
        // Obtener sliders activos
        $sliderProducts = Slider::where('active', true)
            ->orderBy('order')
            ->limit(4)
            ->get();

        // Si no hay sliders, usar productos como respaldo
        if ($sliderProducts->isEmpty()) {
            $sliderProducts = Product::where('active', true)
                ->where('decant', true)
                ->inRandomOrder()
                ->limit(4)
                ->get();
        }

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

        // Obtener promoción activa (una promoción que esté en fecha)
        $activePromotion = Discount::where('startdate', '<=', now())
            ->where('enddate', '>=', now())
            ->orderBy('enddate', 'asc') // Mostrar primero las que van a expirar
            ->first();

        // Si hay promoción activa, obtener un producto de esa promoción
        $promotionProduct = null;
        if ($activePromotion) {
            $promotionProduct = Product::where('iddiscount', $activePromotion->iddiscount)
                ->where('active', true)
                ->inRandomOrder()
                ->first();
            
            // Si la promoción no tiene productos activos, no mostrar nada
            if (!$promotionProduct) {
                $activePromotion = null;
            }
        }

        return view('mainPage.index', compact(
            'sliderProducts',
            'productsForWomen',
            'productsForMen',
            'activePromotion',
            'promotionProduct'
        ));
    }
}