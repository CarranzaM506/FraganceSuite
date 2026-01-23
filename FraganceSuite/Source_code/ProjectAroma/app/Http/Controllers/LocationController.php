<?php

namespace App\Http\Controllers;

use App\Models\Location;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class LocationController extends Controller
{
    public function index()
    {
        return view('profile.location.index');
    }

    public function create()
    {
        
    }
    public function store(Request $request)
    {
        // Validar los datos recibidos
        $validatedData = $request->validate([
            'province' => 'required|string|max:255',
            'canton' => 'required|string|max:255',
            'district' => 'required|string|max:255',
            'detail' => 'required|string|max:500',
            'zipcode' => 'required|max:5|min:5',
        ],[
            'province.required' => 'La provincia es obligatoria.',
            'canton.required' => 'El cantón es obligatorio.',
            'district.required' => 'El distrito es obligatorio.',
            'detail.required' => 'La dirección exacta es obligatoria.',
            'zipcode.required' => 'El código postal es obligatorio.',
            'zipcode.max' => 'El código postal no puede tener más de 5 numeros.',
            'zipcode.min' => 'El codigo postal debe tener al menos 5 numeros.',
        ]);

        // Aquí puedes guardar la dirección en la base de datos asociada al usuario autenticado
        $user = Auth::user();
        $user->locations()->create($validatedData);

        // Redirigir de vuelta con un mensaje de éxito
        return redirect()->route('location.index')->with('success', 'Dirección guardada correctamente.');
        
    }

    public function show(string $id)
    {
        
    }

    public function edit(string $id)
    {
        
    }

    public function update(Request $request, string $id)
    {
         $validatedData = $request->validate([
            'province' => 'required|string|max:255',
            'canton' => 'required|string|max:255',
            'district' => 'required|string|max:255',
            'detail' => 'required|string|max:500',
            'zipcode' => 'required|max:5|min:5',
        ],[
            'province.required' => 'La provincia es obligatoria.',
            'canton.required' => 'El cantón es obligatorio.',
            'district.required' => 'El distrito es obligatorio.',
            'detail.required' => 'La dirección exacta es obligatoria.',
            'zipcode.required' => 'El código postal es obligatorio.',
            'zipcode.max' => 'El código postal no puede tener más de 5 numeros.',
            'zipcode.min' => 'El codigo postal debe tener al menos 5 numeros.',
        ]);

        $l = Location::where('idlocation', $id)->where('iduser', auth()->id())->firstOrFail();
        $l->update($validatedData);
        return redirect()->route('location.index')->with('success', 'Dirección actualizada correctamente.');
    }

    public function destroy(string $id)
    {
        $l = Location::find($id);
        $l->delete();
        return redirect()->route('location.index')->with('success', 'Dirección eliminada correctamente.');
    }
}
