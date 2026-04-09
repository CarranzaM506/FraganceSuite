<?php

namespace App\Http\Controllers;

use App\Models\Review;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;

class ReviewController extends Controller
{
    public function store(Request $request)
    {
        $ratings = ['1', '1.5', '2', '2.5', '3', '3.5', '4', '4.5', '5'];

        $validator = Validator::make($request->all(), [
            'productId' => ['required', 'integer', 'exists:product,idproduct'],
            'rating' => ['required', Rule::in($ratings)],
            'comment' => ['nullable', 'string', 'max:250'],
        ]);

        if ($validator->fails()) {
            return response()->json([
                'error' => 'Datos inválidos',
                'details' => $validator->errors(),
            ], 422);
        }

        $review = Review::create([
            'idproduct' => $request->input('productId'),
            'iduser' => Auth::id(),
            'rating' => $request->input('rating'),
            'comment' => $request->input('comment'),
        ]);

        return response()->json([
            'message' => 'Reseña guardada',
            'reviewId' => $review->idreview,
        ]);
    }
}
