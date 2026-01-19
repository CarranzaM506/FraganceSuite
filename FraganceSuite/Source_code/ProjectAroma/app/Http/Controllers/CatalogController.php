<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Product;

class CatalogController extends Controller
{
    public function index(Request $request)
    {
        $query = Product::query()->where('active', 1);
        
        // Manejar categorías desde el header (mujer, hombre, unisex, kids)
        $categoryFromHeader = strtolower($request->get('category', ''));
        
        if ($categoryFromHeader) {
            switch ($categoryFromHeader) {
                case 'mujer':
                case 'women':
                    // CATEGORÍAS DE MUJER
                    $query->whereIn('category', ['Mujer', 'Mujer>Body Mist', 'Sets Mujer']);
                    break;
                case 'hombre':
                case 'men':
                    // CATEGORÍAS DE HOMBRE
                    $query->whereIn('category', ['Hombre', 'Sets Hombre']);
                    break;
                case 'unisex':
                    // CATEGORÍAS UNISEX
                    $query->whereIn('category', ['Hombre|Mujer', 'Mujer|Hombre']);
                    break;
                case 'kids':
                case 'niños':
                case 'ninos':
                    // CATEGORÍAS DE NIÑOS
                    $query->whereIn('category', ['Niños', 'Mujer|Niños', 'Niños|Mujer']);
                    break;
            }
        }
        // Si no viene del header, usar el filtro normal del modal
        elseif ($request->has('category') && $request->category != 'all') {
            $category = $request->category;
            
            if ($category === 'women') {
                $query->whereIn('category', ['Mujer', 'Mujer>Body Mist', 'Sets Mujer']);
            } 
            elseif ($category === 'men') {
                $query->whereIn('category', ['Hombre', 'Sets Hombre']);
            }
            elseif ($category === 'unisex') {
                $query->whereIn('category', ['Hombre|Mujer', 'Mujer|Hombre']);
            }
            elseif ($category === 'kids') {
                $query->whereIn('category', ['Niños', 'Mujer|Niños', 'Niños|Mujer']);
            }
        }
        
        // Filtrar por marca (tanto del modal como del header)
        if ($request->has('brand') && $request->brand != 'all') {
            $query->where('brand', $request->brand);
        }
        
        // Filtrar por precio
        if ($request->has('price') && $request->price != 'all') {
            switch ($request->price) {
                case '0-5000':
                    $query->whereBetween('price', [0, 5000]);
                    break;
                case '5000-10000':
                    $query->whereBetween('price', [5000, 10000]);
                    break;
                case '10000-20000':
                    $query->whereBetween('price', [10000, 20000]);
                    break;
                case '20000-50000':
                    $query->whereBetween('price', [20000, 50000]);
                    break;
                case '50000-plus':
                    $query->where('price', '>', 50000);
                    break;
            }
        }
        
        // Buscar por nombre o marca (del header o del modal)
        if ($request->has('search') && !empty($request->search)) {
            $searchTerm = $request->search;
            $query->where(function($q) use ($searchTerm) {
                $q->where('name', 'like', '%' . $searchTerm . '%')
                  ->orWhere('brand', 'like', '%' . $searchTerm . '%');
            });
        }
        
        // Ordenar
        switch ($request->get('sort', 'newest')) {
            case 'price-asc':
                $query->orderBy('price', 'asc');
                break;
            case 'price-desc':
                $query->orderBy('price', 'desc');
                break;
            case 'name-asc':
                $query->orderBy('name', 'asc');
                break;
            case 'name-desc':
                $query->orderBy('name', 'desc');
                break;
            default:
                $query->orderBy('idproduct', 'desc'); // Más recientes
        }
        
        // Paginación 
        $products = $query->paginate(12)->withQueryString();
        
        // Obtener marcas únicas para los filtros
        $brands = Product::where('active', 1)
            ->distinct()
            ->pluck('brand')
            ->filter()
            ->sort()
            ->values()
            ->toArray();
        
        $categoryCounts = $this->getCategoryCounts();
        return view('catalog.index', compact('products', 'brands', 'categoryCounts'));
    }
    
    /*
     * Obtener conteos dinámicos de productos por categoría
     */
    private function getCategoryCounts()
    {
        $total = Product::where('active', 1)->count();
        
        $womenCount = Product::where('active', 1)
            ->whereIn('category', ['Mujer', 'Mujer>Body Mist', 'Sets Mujer'])
            ->count();
            
        $menCount = Product::where('active', 1)
            ->whereIn('category', ['Hombre', 'Sets Hombre'])
            ->count();
            
        $unisexCount = Product::where('active', 1)
            ->whereIn('category', ['Hombre|Mujer', 'Mujer|Hombre'])
            ->count();
            
        $kidsCount = Product::where('active', 1)
            ->whereIn('category', ['Niños', 'Mujer|Niños', 'Niños|Mujer'])
            ->count();
        
        return [
            'total' => $total,
            'women' => $womenCount,
            'men' => $menCount,
            'unisex' => $unisexCount,
            'kids' => $kidsCount
        ];
    }
}