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

    public function create() {}
    public function store(Request $request)
    {
        // Validar los datos recibidos
        $validatedData = $request->validate([
            'province' => ['bail', 'required', 'string', 'min:2', 'max:100', 'regex:/^(?!\s+$)[A-Za-zÁÉÍÓÚáéíóúÑñÜü\s]+$/u',],
            'canton' => ['bail', 'required', 'string', 'min:2', 'max:100', 'regex:/^(?!\s+$)[A-Za-zÁÉÍÓÚáéíóúÑñÜü0-9\s\-]+$/u',],
            'district' => ['bail', 'required', 'string', 'min:2', 'max:100', 'regex:/^(?!\s+$)[A-Za-zÁÉÍÓÚáéíóúÑñÜü0-9\s\-]+$/u',],
            'detail' => ['bail', 'required', 'string', 'min:10', 'max:500', 'regex:/^(?!\s+$).+/u',],
            'zipcode' => ['bail', 'required', 'string', 'size:5', 'regex:/^[1-7][0-9]{4}$/',],
        ], [
            'province.required' => 'La provincia es obligatoria.',
            'province.string' => 'La provincia debe ser texto.',
            'province.min' => 'La provincia debe tener al menos 2 caracteres.',
            'province.max' => 'La provincia no puede tener más de 100 caracteres.',
            'province.regex' => 'La provincia solo puede contener letras.',

            'canton.required' => 'El cantón es obligatorio.',
            'canton.string' => 'El cantón debe ser texto.',
            'canton.min' => 'El cantón debe tener al menos 2 caracteres.',
            'canton.max' => 'El cantón no puede tener más de 100 caracteres.',
            'canton.regex' => 'El cantón solo puede contener letras.',

            'district.required' => 'El distrito es obligatorio.',
            'district.string' => 'El distrito debe ser texto.',
            'district.min' => 'El distrito debe tener al menos 2 caracteres.',
            'district.max' => 'El distrito no puede tener más de 100 caracteres.',
            'district.regex' => 'El distrito solo puede contener letras.',

            'detail.required' => 'La dirección exacta es obligatoria.',
            'detail.string' => 'La dirección exacta debe ser texto.',
            'detail.min' => 'La dirección exacta debe tener al menos 10 caracteres.',
            'detail.max' => 'La dirección exacta no puede tener más de 500 caracteres.',
            'detail.regex' => 'La dirección exacta no puede estar vacía o solo contener espacios.',

            'zipcode.required' => 'El código postal es obligatorio.',
            'zipcode.string' => 'El código postal debe ser texto.',
            'zipcode.size' => 'El código postal debe tener exactamente 5 dígitos.',
            'zipcode.regex' => 'El código postal debe ser válido en Costa Rica y contener solo números.',
        ]);


        // Aquí puedes guardar la dirección en la base de datos asociada al usuario autenticado
        $user = Auth::user();
        $user->locations()->create($validatedData);

        // Redirigir de vuelta con un mensaje de éxito
        return redirect()->route('location.index')->with('success', 'Dirección guardada correctamente.');
    }

    public function storeApi(Request $request)
    {
        $validatedData = $request->validate([
            'province' => ['bail', 'required', 'string', 'min:5', 'max:100', 'regex:/^(?!\s+$)[A-Za-zÁÉÍÓÚáéíóúÑñÜü\s]+$/u',],
            'canton' => ['bail', 'required', 'string', 'min:5', 'max:100', 'regex:/^(?!\s+$)[A-Za-zÁÉÍÓÚáéíóúÑñÜü0-9\s\-]+$/u',],
            'district' => ['bail', 'required', 'string', 'min:5', 'max:100', 'regex:/^(?!\s+$)[A-Za-zÁÉÍÓÚáéíóúÑñÜü0-9\s\-]+$/u',],
            'detail' => ['bail', 'required', 'string', 'min:10', 'max:500', 'regex:/^(?!\s+$).+/u',],
            'zipcode' => ['bail', 'required', 'string', 'size:5', 'regex:/^[1-7][0-9]{4}$/',],
        ], [
            'province.required' => 'La provincia es obligatoria.',
            'province.string' => 'La provincia debe ser texto.',
            'province.min' => 'La provincia debe tener al menos 5 caracteres.',
            'province.max' => 'La provincia no puede tener más de 100 caracteres.',
            'province.regex' => 'La provincia solo puede contener letras.',

            'canton.required' => 'El cantón es obligatorio.',
            'canton.string' => 'El cantón debe ser texto.',
            'canton.min' => 'El cantón debe tener al menos 5 caracteres.',
            'canton.max' => 'El cantón no puede tener más de 100 caracteres.',
            'canton.regex' => 'El cantón solo puede contener letras.',

            'district.required' => 'El distrito es obligatorio.',
            'district.string' => 'El distrito debe ser texto.',
            'district.min' => 'El distrito debe tener al menos 5 caracteres.',
            'district.max' => 'El distrito no puede tener más de 100 caracteres.',
            'district.regex' => 'El distrito solo puede contener letras.',

            'detail.required' => 'La dirección exacta es obligatoria.',
            'detail.string' => 'La dirección exacta debe ser texto.',
            'detail.min' => 'La dirección exacta debe tener al menos 10 caracteres.',
            'detail.max' => 'La dirección exacta no puede tener más de 500 caracteres.',
            'detail.regex' => 'La dirección exacta no puede estar vacía o solo contener espacios.',

            'zipcode.required' => 'El código postal es obligatorio.',
            'zipcode.string' => 'El código postal debe ser texto.',
            'zipcode.size' => 'El código postal debe tener exactamente 5 dígitos.',
            'zipcode.regex' => 'El código postal debe ser válido en Costa Rica y contener solo números.',
        ]);

        $location = auth()->user()->locations()->create($validatedData);

        return response()->json([
            'success' => true,
            'message' => 'Dirección guardada correctamente',
            'location' => $location
        ]);
    }

    public function show(string $id) {}

    public function edit(string $id) {}

    public function update(Request $request, string $id)
    {
        $validatedData = $request->validate([
            'province' => 'required|string|max:255',
            'canton' => 'required|string|max:255',
            'district' => 'required|string|max:255',
            'detail' => 'required|string|max:500',
            'zipcode' => 'required|max:5|min:5',
        ], [
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
