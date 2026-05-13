<?php

namespace App\Http\Controllers;

use App\Models\Hero;
use Illuminate\Http\Request;

class HeroController extends Controller
{
    /**
     * Mostrar lista de imágenes hero (dashboard)
     */
    public function index()
    {
        $heroes = Hero::all();

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

        $fileName = null;
        if ($request->hasFile('image')) {
            if (!is_dir(public_path('img-hero'))) {
                mkdir(public_path('img-hero'), 0755, true);
            }

            $file     = $request->file('image');
            $fileName = time() . '_' . str_replace(' ', '_', $file->getClientOriginalName());
            $file->move(public_path('img-hero'), $fileName);
        }

        Hero::create([
            'title'  => $request->title,
            'image'  => $fileName,
            'active' => $request->has('active') ? 1 : 0,
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
        
        if ($hero->image) {
            $path = public_path('img-hero/' . $hero->image);
            if (file_exists($path)) {
                unlink($path);
            }
        }
        
        // Si el hero eliminado estaba activo, no pasa nada (ya no hay hero visible)
        $hero->delete();
        
        return redirect()->route('hero.index')
            ->with('success', 'Hero eliminado exitosamente.');
    }
}