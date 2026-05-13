<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class CheckOutController extends Controller
{
    public function index()
    {
        $user = Auth::user();

        // traer direcciones del usuario SIN duplicados
        $addresses = $user->locations()->distinct()->get();

        return view('cart.checkout', compact('addresses'));
    }
}