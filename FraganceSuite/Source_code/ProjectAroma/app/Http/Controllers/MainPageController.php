<?php

namespace App\Http\Controllers;

use App\Models\Product;
use App\Models\Hero; // CAMBIADO de Slider a Hero
use App\Models\Discount;
use Illuminate\Support\Str;
use Illuminate\Http\Request;

class MainPageController extends Controller
{
    public function index()
    {
        // OBTENER HERO ACTIVO - SOLO 1 IMAGEN
        $heroImage = Hero::where('active', true)->first();

        // Si NO hay hero activo, usar un producto como respaldo
        if (!$heroImage) {
            $product = Product::where('active', true)
                ->where('decant', true)
                ->inRandomOrder()
                ->first();
                
            // Crear objeto hero virtual con la imagen del producto
            $heroImage = (object)[
                'image' => $product ? str_replace('public/storage/', '', $product->pathimg) : null,
                'title' => 'AROMA'
            ];
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

        // Obtener promoción activa
        $activePromotion = Discount::where('startdate', '<=', now())
            ->where('enddate', '>=', now())
            ->orderBy('enddate', 'asc')
            ->first();

        // Producto de la promoción
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
            'heroImage', // CAMBIADO de sliderProducts a heroImage
            'productsForWomen',
            'productsForMen',
            'activePromotion',
            'promotionProduct'
        ));
    }
}