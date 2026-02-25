<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ProfileController extends Controller
{

    public function index()
    {
        return view('profile.index');
    }

    public function create() {}


    public function store(Request $request) {}

    public function show(string $id) {}

    public function edit()
    {
        return view('profile.edit');
    }

    public function update(Request $request)
    {
        $request->validate(
            [
                'name' => 'required|string|max:255',
                'lastname' => 'nullable|string|max:255',
                'phone' => 'required|string|digits:8',
            ],
            [
                'name.required' => 'El nombre es obligatorio.',
                'phone.required' => 'El telefono es obligatorio.',
                'phone.digits' => 'El telofono debe contener 8 numeros.'
            ]
        );

        $user = Auth::user();
        $user->name = $request->input('name');
        $user->lastname = $request->input('lastname');
        $user->phone = $request->input('phone');
        $user->save();

        return redirect()->route('profile.index')->with('success', 'Perfil actualizado correctamente.');
    }

    public function destroy(string $id) {

    }
}
