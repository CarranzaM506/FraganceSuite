<?php
// app/Http/Controllers/FavoriteController.php

namespace App\Http\Controllers;

use App\Models\Favorite;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

class FavoriteController extends Controller
{
    

public function toggle(Request $request)
{
    try {
        $user = Auth::user();
        
        if (!$user) {
            return response()->json([
                'success' => false,
                'error' => 'No autenticado'
            ], 401);
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
            // Eliminar
            $favorite->delete();
            return response()->json([
                'success' => true,
                'status' => 'removed',
                'message' => 'Eliminado de favoritos'
            ]);
        } else {
            // Crear
            Favorite::create([
                'iduser' => $user->id,
                'idproduct' => $productId
            ]);
            return response()->json([
                'success' => true,
                'status' => 'added',
                'message' => 'Añadido a favoritos'
            ]);
        }
    } catch (\Exception $e) {
        return response()->json([
            'success' => false,
            'error' => 'Error al procesar'
        ], 500);
    }
}

    /**
     * Check if a product is in user's favorites
     */
    public function check($productId)
    {
        try {
            $user = Auth::user();
            
            if (!$user) {
                return response()->json([
                    'isFavorite' => false,
                    'authenticated' => false
                ]);
            }

            $isFavorite = Favorite::where('iduser', $user->id)
                ->where('idproduct', $productId)
                ->exists();

            return response()->json([
                'isFavorite' => $isFavorite,
                'authenticated' => true
            ]);
        } catch (\Exception $e) {
            Log::error('Error checking favorite: ' . $e->getMessage());
            return response()->json([
                'isFavorite' => false,
                'error' => 'Error al verificar'
            ], 500);
        }
    }
}