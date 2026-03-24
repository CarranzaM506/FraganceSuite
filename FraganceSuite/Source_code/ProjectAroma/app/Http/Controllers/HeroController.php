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