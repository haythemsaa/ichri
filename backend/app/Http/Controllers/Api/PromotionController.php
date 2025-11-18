<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Promotion;
use Illuminate\Http\Request;

class PromotionController extends Controller
{
    /**
     * Get active promotions
     */
    public function index()
    {
        $promotions = Promotion::active()
            ->with(['products.images', 'categories'])
            ->orderBy('is_featured', 'desc')
            ->orderBy('created_at', 'desc')
            ->get();

        return response()->json([
            'success' => true,
            'data' => $promotions
        ]);
    }

    /**
     * Get featured promotions
     */
    public function featured()
    {
        $promotions = Promotion::active()
            ->featured()
            ->with(['products.images'])
            ->limit(5)
            ->get();

        return response()->json([
            'success' => true,
            'data' => $promotions
        ]);
    }

    /**
     * Validate promotion code
     */
    public function validateCode(Request $request)
    {
        $code = $request->input('code');

        $promotion = Promotion::active()
            ->byCode($code)
            ->first();

        if (!$promotion) {
            return response()->json([
                'success' => false,
                'message' => 'Code promo invalide ou expiré'
            ], 404);
        }

        if (!$promotion->canBeUsedBy(auth()->id())) {
            return response()->json([
                'success' => false,
                'message' => 'Vous avez déjà utilisé ce code promo'
            ], 400);
        }

        return response()->json([
            'success' => true,
            'data' => $promotion
        ]);
    }

    /**
     * Apply promotion to cart
     */
    public function apply(Request $request)
    {
        $code = $request->input('code');
        $orderAmount = $request->input('order_amount');
        $items = $request->input('items', []);

        $promotion = Promotion::active()->byCode($code)->first();

        if (!$promotion) {
            return response()->json([
                'success' => false,
                'message' => 'Code promo invalide'
            ], 404);
        }

        if (!$promotion->canBeUsedBy(auth()->id())) {
            return response()->json([
                'success' => false,
                'message' => 'Code promo non utilisable'
            ], 400);
        }

        $discount = $promotion->calculateDiscount($orderAmount, $items);

        return response()->json([
            'success' => true,
            'data' => [
                'promotion' => $promotion,
                'discount_amount' => $discount,
                'new_total' => max(0, $orderAmount - $discount),
            ]
        ]);
    }
}
