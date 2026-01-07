<?php

namespace App\Http\Controllers;

use App\Models\Product;
use Illuminate\Http\Request;

class ControllerProduct extends Controller
{
    
    public function index()
    {

    }

    public function create()
    {
        return view('dashboard.product.create');
    }

    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'brand' => 'required|string|max:50',
            'category' => 'required|string|max:50',
            'pathimg' => 'required|url|max:2048',
            'price' => 'required|numeric|min:1',
            'stock' => 'required|integer|min:1',
            'description' => 'required|string|max: 255',
            'shortDescription' => 'nullable|string|max:100'

        ],[
        // name
        'name.required' => 'El nombre del producto es obligatorio.',
        'name.string'   => 'El nombre del producto debe ser texto.',
        'name.max'      => 'El nombre del producto no puede superar 255 caracteres.',

        // brand
        'brand.required' => 'La marca es obligatoria.',
        'brand.string'   => 'La marca debe ser texto.',
        'brand.max'      => 'La marca no puede superar 50 caracteres.',

        // category
        'category.required' => 'La categoría es obligatoria.',
        'category.string'   => 'La categoría debe ser texto.',
        'category.max'      => 'La categoría no puede superar 50 caracteres.',

        // pathimg
        'pathimg.required' => 'La imagen es obligatoria.',
        'pathimg.url'      => 'La ruta de la imagen debe ser una URL válida.',
        'pathimg.max'      => 'La URL de la imagen es demasiado larga.',

        // price
        'price.required' => 'El precio es obligatorio.',
        'price.numeric'  => 'El precio debe ser un número válido.',
        'price.min'      => 'El precio no puede ser negativo.',

        // stock
        'stock.required' => 'El stock es obligatorio.',
        'stock.integer'  => 'El stock debe ser un número entero.',
        'stock.min'      => 'El stock no puede ser negativo.',

        // description
        'description.required' => 'La descripción es obligatoria.',
        'description.string'   => 'La descripción debe ser texto.',
        'description.max'      => 'La descripción no puede superar 255 caracteres.',

        // shortDescription
        'shortDescription.string' => 'La descripción corta debe ser texto.',
        'shortDescription.max'    => 'La descripción corta no puede superar 100 caracteres.',
        ]);

        $p = new Product();

        $p->name = $request->name;
        $p->brand = $request->brand;
        $p->category = $request->category;
        $p->pathimg = $request->pathimg;
        $p->price = $request->price;
        $p->stock = $request->stock;
        $p->description = $request->description;
        $p->shortDescription = $request->shortDescription;
        $p->decant = $request->boolean('decant');
        $p->active = $request->boolean('active');
        $p->save();

        return redirect()->route('product.create')->with('success', 'Producto creado exitosamente.');

    }

    public function show(string $id)
    {
        
    }

    public function edit(string $id)
    {
        
    }

    public function update(Request $request, string $id)
    {
        
    }

    public function destroy(string $id)
    {
        
    }
}
