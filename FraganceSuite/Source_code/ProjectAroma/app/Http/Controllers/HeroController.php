<?php

namespace App\Http\Controllers;

use App\Models\Hero;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class HeroController extends Controller
{
    /**
     * Mostrar lista de imágenes hero (dashboard)
     */
    public function index()
    {
        $heroes = Hero::all(); // Sin order, solo todas
        return view('dashboard.hero.index', compact('heroes'));
    }

    /**
     * Mostrar formulario de creación
     */
    public function create()
    {
        return view('dashboard.hero.create');
    }

    /**
     * Guardar nueva imagen hero
     */
    public function store(Request $request)
    {
        $request->validate([
            'image' => 'required|image|mimes:jpg,jpeg,png,gif,webp|max:2048',
            'title' => 'nullable|string|max:255'
        ]);

        // Si esta nueva imagen se marca como activa, desactivar todas las demás
        if ($request->has('active')) {
            Hero::where('active', 1)->update(['active' => 0]);
        }

        // Guardar imagen
        $imagePath = null;
        if ($request->hasFile('image')) {
            $imagePath = $request->file('image')->store('hero', 'public');
        }

        // Crear registro
        Hero::create([
            'title' => $request->title,
            'image' => $imagePath,
            'active' => $request->has('active') ? 1 : 0
        ]);

        return redirect()->route('hero.index')
            ->with('success', 'Hero image agregada correctamente');
    }

    /**
     * Mostrar formulario de edición
     */
    public function edit(string $id)
    {
        $hero = Hero::findOrFail($id);
        return view('dashboard.hero.edit', compact('hero'));
    }

    /**
     * Actualizar imagen hero
     */
    public function update(Request $request, string $id)
    {
        $request->validate([
            'title' => 'nullable|string|max:255',
            'image' => 'nullable|image|mimes:jpeg,png,jpg,gif,webp|max:2048'
        ]);

        $hero = Hero::findOrFail($id);

        // Si se activa esta imagen, desactivar todas las demás
        if ($request->has('active') && !$hero->active) {
            Hero::where('active', 1)->update(['active' => 0]);
        }

        $data = [
            'title' => $request->title,
            'active' => $request->has('active') ? 1 : 0
        ];

        // Si hay nueva imagen
        if ($request->hasFile('image')) {
            // Eliminar imagen anterior
            if ($hero->image) {
                Storage::disk('public')->delete($hero->image);
            }
            $data['image'] = $request->file('image')->store('hero', 'public');
        }

        $hero->update($data);

        return redirect()->route('hero.index')
            ->with('success', 'Hero actualizado exitosamente.');
    }

    /**
     * Eliminar imagen hero
     */
    public function destroy(string $id)
    {
        $hero = Hero::findOrFail($id);
        
        // Eliminar archivo de imagen
        if ($hero->image) {
            Storage::disk('public')->delete($hero->image);
        }
        
        // Si el hero eliminado estaba activo, no pasa nada (ya no hay hero visible)
        $hero->delete();
        
        return redirect()->route('hero.index')
            ->with('success', 'Hero eliminado exitosamente.');
    }
}