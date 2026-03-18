<?php

namespace App\Http\Controllers;

use App\Models\Favorite;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class FavoriteController extends Controller
{
    public function toggle(Request $request)
    {
        $user = Auth::user();
        
        if (!$user) {
            return response()->json(['error' => 'No autenticado'], 401);
        }

        $request->validate([
            'productId' => 'required|integer'
        ]);

        $productId = $request->productId;

        // Verificar si ya existe
        $favorite = Favorite::where('iduser', $user->id)
            ->where('idproduct', $productId)
            ->first();

        if ($favorite) {
            // Si existe, lo eliminamos
            $favorite->delete();
            return response()->json(['status' => 'removed']);
        } else {
            // Si no existe, lo creamos
            Favorite::create([
                'iduser' => $user->id,
                'idproduct' => $productId
            ]);
            return response()->json(['status' => 'added']);
        }
    }

    // Método para verificar si un producto está en favoritos
    public function check($productId)
    {
        $user = Auth::user();
        
        if (!$user) {
            return response()->json(['isFavorite' => false]);
        }

        $isFavorite = Favorite::where('iduser', $user->id)
            ->where('idproduct', $productId)
            ->exists();

        return response()->json(['isFavorite' => $isFavorite]);
    }
}