<?php

namespace App\Http\Controllers;

use App\Models\Location;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

class LocationController extends Controller
{
    public function index()
    {
        return view('profile.location.index');
    }

    public function create() {}
    
    public function store(Request $request)
    {
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

        $user = Auth::user();
        $user->locations()->create($validatedData);

        return redirect()->route('location.index')->with('success', 'Dirección guardada correctamente.');
    }

    public function storeApi(Request $request)
    {
        try {
            $validatedData = $request->validate([
                'province' => ['required', 'string', 'min:2', 'max:100'],
                'canton' => ['required', 'string', 'min:2', 'max:100'],
                'district' => ['required', 'string', 'min:2', 'max:100'],
                'detail' => ['required', 'string', 'min:10', 'max:500'],
                'zipcode' => ['required', 'string', 'size:5'],
            ]);

            $user = auth()->user();
            
            $province = trim($validatedData['province']);
            $canton = trim($validatedData['canton']);
            $district = trim($validatedData['district']);
            $detail = trim($validatedData['detail']);
            $zipcode = $validatedData['zipcode'];
            
            $existing = $user->locations()
                ->where('province', $province)
                ->where('canton', $canton)
                ->where('district', $district)
                ->where('detail', $detail)
                ->where('zipcode', $zipcode)
                ->first();
            
            if ($existing) {
                return response()->json([
                    'success' => false,
                    'message' => 'Esta dirección ya está registrada'
                ], 409);
            }
            
            $location = $user->locations()->create([
                'province' => $province,
                'canton' => $canton,
                'district' => $district,
                'detail' => $detail,
                'zipcode' => $zipcode
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Dirección guardada correctamente',
                'location' => $location
            ]);
            
        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Complete todos los campos correctamente'
            ], 422);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al guardar la dirección'
            ], 500);
        }
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