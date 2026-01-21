<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Auth\Events\Registered;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules;
use Illuminate\View\View;

class RegisteredUserController extends Controller
{
    public function create(): View
    {
        return view('auth.register');
    }

    public function store(Request $request): RedirectResponse
    {
        
        $request->validate(
    [
        'name' => ['required', 'string', 'max:255'],
        'lastname' => ['required', 'string', 'max:255'],
        'email' => ['required', 'string', 'lowercase', 'email', 'max:255', 'unique:' . User::class],
        'password' => ['required', 'confirmed', Rules\Password::defaults()],
    ],
    [
        'name.required' => 'El nombre es obligatorio.',
        'name.string'   => 'El nombre debe ser texto.',
        'name.max'      => 'El nombre no puede superar los 255 caracteres.',

        'lastname.required' => 'El apellido es obligatorio.',
        'lastname.string'   => 'El apellido debe ser texto.',
        'lastname.max'      => 'El apellido no puede superar los 255 caracteres.',

        'email.required' => 'El correo electrónico es obligatorio.',
        'email.email'    => 'Debe ingresar un correo electrónico válido.',
        'email.unique'   => 'Este correo ya está registrado.',
        'email.lowercase'=> 'El correo electrónico debe estar en minúsculas.',
        'email.max'      => 'El correo electrónico no puede superar los 255 caracteres.',

        'password.required'  => 'La contraseña es obligatoria.',
        'password.confirmed' => 'Las contraseñas no coinciden.',
        'password.min'       => 'La contraseña debe tener al menos :min caracteres.',
    ]
);


        $user = User::create([
            'name' => $request->name,
            'lastname' => $request->lastname,
            'email' => $request->email,
            'password' => $request->password, // cast hashed
            'type' => 0, // cliente
        ]);

        Auth::login($user);

        return redirect('/');
    }
}
