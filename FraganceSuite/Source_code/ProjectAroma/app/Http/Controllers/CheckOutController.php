<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class CheckOutController extends Controller
{
    public function index()
    {
        $user =  Auth::user();

        // traer direcciones del usuario
        $addresses = $user->locations;

        return view('cart.checkout', compact('addresses'));
    }
}
