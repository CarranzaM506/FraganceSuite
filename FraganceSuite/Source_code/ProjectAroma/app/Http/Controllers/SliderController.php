<?php

namespace App\Http\Controllers;

use App\Models\Slider;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class SliderController extends Controller
{
    public function index()
    {
        $sliders = Slider::orderBy('order')->get();
        return view('dashboard.slider.index', compact('sliders'));
    }

    public function create()
    {
        return view('dashboard.slider.create');
    }

    public function store(Request $request)
    {
        $request->validate([
            'title' => 'nullable|string|max:255',
            'image' => 'required|image|mimes:jpeg,png,jpg,gif,webp|max:2048',
            'order' => 'nullable|integer',
        ]);

        // Subir imagen
        $imagePath = null;
        if ($request->hasFile('image')) {
            $imagePath = $request->file('image')->store('sliders', 'public');
        }

        Slider::create([
            'title' => $request->title,
            'image_url' => $imagePath ? Storage::url($imagePath) : null,
            'order' => $request->order ?? 0,
            'active' => $request->has('active'),
        ]);

        return redirect()->route('slider.index')->with('success', 'Imagen del slider guardada exitosamente.');
    }

    public function edit(string $id)
    {
        $slider = Slider::findOrFail($id);
        return view('dashboard.slider.edit', compact('slider'));
    }

    public function update(Request $request, string $id)
    {
        $request->validate([
            'title' => 'nullable|string|max:255',
            'image' => 'nullable|image|mimes:jpeg,png,jpg,gif,webp|max:2048',
            'order' => 'nullable|integer',
        ]);

        $slider = Slider::findOrFail($id);

        $data = [
            'title' => $request->title,
            'order' => $request->order ?? 0,
            'active' => $request->has('active'),
        ];

        // Si hay nueva imagen
        if ($request->hasFile('image')) {
            // Eliminar imagen anterior si existe
            if ($slider->image_url) {
                $oldImage = str_replace('/storage/', '', $slider->image_url);
                Storage::disk('public')->delete($oldImage);
            }
            
            $imagePath = $request->file('image')->store('sliders', 'public');
            $data['image_url'] = Storage::url($imagePath);
        }

        $slider->update($data);

        return redirect()->route('slider.index')->with('success', 'Slider actualizado exitosamente.');
    }

    public function destroy(string $id)
    {
        $slider = Slider::findOrFail($id);
        
        // Eliminar imagen
        if ($slider->image_url) {
            $oldImage = str_replace('/storage/', '', $slider->image_url);
            Storage::disk('public')->delete($oldImage);
        }
        
        $slider->delete();
        
        return redirect()->route('slider.index')->with('success', 'Slider eliminado exitosamente.');
    }
}