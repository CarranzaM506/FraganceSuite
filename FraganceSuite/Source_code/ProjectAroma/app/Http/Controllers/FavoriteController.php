<?php
// app/Http/Controllers/FavoriteController.php

namespace App\Http\Controllers;

use App\Models\Favorite;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;

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
                // ELIMINAR - Usando query builder directamente para evitar el error de primary key
                $deleted = DB::table('favorite')
                    ->where('iduser', $user->id)
                    ->where('idproduct', $productId)
                    ->delete();
                
                Log::info('Eliminado:', ['deleted' => $deleted]);
                
                return response()->json([
                    'success' => true,
                    'status' => 'removed',
                    'message' => 'Eliminado de favoritos'
                ]);
            } else {
                // AÑADIR
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
            Log::error('Error en favoritos: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'error' => 'Error al procesar: ' . $e->getMessage()
            ], 500);
        }
    }

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