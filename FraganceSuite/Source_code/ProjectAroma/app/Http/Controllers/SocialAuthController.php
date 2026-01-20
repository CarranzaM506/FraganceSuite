<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Laravel\Socialite\Facades\Socialite;

class SocialAuthController extends Controller
{
    public function redirect($provider)
    {
        return Socialite::driver($provider)->redirect();
    }

    public function callback($provider)
    {

        $socialUser = Socialite::driver($provider)->user();

        $user = User::where('email', $socialUser->getEmail())->first();

        if ($user && $user->provider === null) {
            return redirect()->route('login')->withErrors(['error' => 'Este correo ya esta registrado. Ingrese con su contraseña.']);
        }

        $user = User::updateOrCreate(
            ['email' => $socialUser->getEmail()],

            [
                'name' => $socialUser->getName(),
                'provider' => $provider,
                'provider_id' => $socialUser->getId(),
                'password' => null,
                'type' => 0,
            ]

        );

        Auth::login($user, true);

        return redirect()->route(Auth::user()->type === 0 ? 'mainPage' : 'dashboard');
    }
}
