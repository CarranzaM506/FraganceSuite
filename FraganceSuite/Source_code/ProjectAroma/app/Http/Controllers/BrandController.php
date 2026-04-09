<?php

namespace App\Http\Controllers;

use App\Models\Brand;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class BrandController extends Controller
{
    public function index()
    {
        // Obtener todas las marcas de productos (para mostrar cuáles faltan)
        $existingBrands = \App\Models\Product::select('brand')
            ->whereNotNull('brand')
            ->where('brand', '!=', '')
            ->distinct()
            ->pluck('brand');
        
        $brands = Brand::orderBy('order')->orderBy('brand_name')->get();
        
        return view('dashboard.brands.index', compact('brands', 'existingBrands'));
    }

    public function edit($id)
    {
        $brand = Brand::findOrFail($id);
        return view('dashboard.brands.edit', compact('brand'));
    }

    public function update(Request $request, $id)
    {
        $brand = Brand::findOrFail($id);
        
        $request->validate([
            'logo' => 'required|image|mimes:png,jpg,jpeg,svg,webp|max:2048',
            'order' => 'integer',
            'active' => 'boolean'
        ]);

        // Subir nuevo logo
        if ($request->hasFile('logo')) {
            // Eliminar logo anterior
            if ($brand->logo && Storage::disk('public')->exists($brand->logo)) {
                Storage::disk('public')->delete($brand->logo);
            }
            $path = $request->file('logo')->store('brands', 'public');
            $brand->logo = $path;
        }

        $brand->order = $request->order ?? 0;
        $brand->active = $request->has('active');
        $brand->save();

        return redirect()->route('brands.index')->with('success', 'Logo actualizado correctamente');
    }

    // Sincronizar marcas: crear registros para marcas nuevas que aparecen en productos
    public function sync()
    {
        $productBrands = \App\Models\Product::select('brand')
            ->whereNotNull('brand')
            ->where('brand', '!=', '')
            ->distinct()
            ->pluck('brand');
        
        foreach ($productBrands as $brandName) {
            Brand::firstOrCreate(
                ['brand_name' => $brandName],
                ['active' => true, 'order' => 0]
            );
        }
        
        return redirect()->route('brands.index')->with('success', 'Marcas sincronizadas correctamente');
    }
}