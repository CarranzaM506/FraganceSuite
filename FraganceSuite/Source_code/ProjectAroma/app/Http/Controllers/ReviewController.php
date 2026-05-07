<?php

namespace App\Http\Controllers;

use App\Models\Review;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;

class ReviewController extends Controller
{
    private const MODERATION_PATTERNS = [
        'sexual' => [
            '/\b(sex|sexy|porn|porno|xxx|nude|desnudo|desnuda|onlyfans)\b/i',
        ],
        'bad_words' => [
            '/\b(mierda|puta|puto|hijueputa|jueputa|idiota|estupido|estupida|imbecil|pendejo|pendeja|fuck|fucking|shit|bitch|asshole|bastard)\b/i',
        ],
        'hate_speech' => [
            '/\b(nazi|white power|supremac|racista|racismo|odio a|hate)\b/i',
        ],
        'self_harm' => [
            '/\b(suicid|matarme|matarse|quitarme la vida|quitarse la vida|kill myself|self harm)\b/i',
        ],
    ];

    private const MODERATION_REASON_LABELS = [
        'sexual' => 'Contenido sexual',
        'bad_words' => 'Lenguaje ofensivo',
        'hate_speech' => 'Discurso de odio',
        'self_harm' => 'Riesgo de autolesion/violencia',
    ];

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
                'error' => 'Datos invalidos',
                'details' => $validator->errors(),
            ], 422);
        }

        $comment = $request->input('comment');
        $moderation = $this->moderateComment($comment);

        $review = Review::create([
            'idproduct' => $request->input('productId'),
            'iduser' => Auth::id(),
            'rating' => $request->input('rating'),
            'comment' => $comment,
            'is_blocked' => $moderation['blocked'],
            'moderation_reason' => $moderation['reason'],
        ]);

        if ($moderation['blocked']) {
            return response()->json([
                'message' => 'Resena recibida. Fue bloqueada automaticamente por normas de comunidad y esta en revision de administracion.',
                'reviewId' => $review->idreview,
                'blocked' => true,
            ]);
        }

        return response()->json([
            'message' => 'Resena guardada',
            'reviewId' => $review->idreview,
            'blocked' => false,
        ]);
    }

    public function getByProduct($productId)
    {
        $reviews = Review::where('idproduct', $productId)
            ->where(function ($query) {
                $query->whereNull('is_blocked')
                    ->orWhere('is_blocked', 0);
            })
            ->with('user')
            ->orderBy('created_at', 'desc')
            ->get();

        return response()->json($reviews);
    }

    public function adminIndex(Request $request)
    {
        $status = $request->query('status', 'blocked');

        $query = Review::with(['user:id,name,lastname,email', 'product:idproduct,name'])
            ->orderByDesc('created_at');

        if ($status === 'blocked') {
            $query->where('is_blocked', 1);
        } elseif ($status === 'visible') {
            $query->where(function ($q) {
                $q->whereNull('is_blocked')->orWhere('is_blocked', 0);
            });
        }

        $reviews = $query->paginate(20)->withQueryString();

        return view('dashboard.reviews.index', compact('reviews', 'status'));
    }

    public function destroy($idreview)
    {
        $review = Review::findOrFail($idreview);
        $review->delete();

        return redirect()
            ->route('dashboard.reviews.index')
            ->with('status', 'Resena eliminada correctamente.');
    }

    private function moderateComment(?string $comment): array
    {
        if (blank($comment)) {
            return ['blocked' => false, 'reason' => null];
        }

        $normalized = Str::of($comment)->ascii()->lower()->toString();

        foreach (self::MODERATION_PATTERNS as $reasonKey => $patterns) {
            foreach ($patterns as $pattern) {
                if (preg_match($pattern, $normalized) === 1) {
                    return [
                        'blocked' => true,
                        'reason' => self::MODERATION_REASON_LABELS[$reasonKey] ?? 'Contenido bloqueado',
                    ];
                }
            }
        }

        return ['blocked' => false, 'reason' => null];
    }
}
