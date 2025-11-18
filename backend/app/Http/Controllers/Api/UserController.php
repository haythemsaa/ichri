<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class UserController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth:api');
    }

    /**
     * Get user profile
     */
    public function profile()
    {
        $user = auth()->user()->load(['addresses', 'documents']);

        return response()->json([
            'success' => true,
            'data' => $user
        ]);
    }

    /**
     * Update user profile
     */
    public function updateProfile(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'first_name' => 'nullable|string|max:255',
            'last_name' => 'nullable|string|max:255',
            'email' => 'nullable|email|unique:users,email,' . auth()->id(),
            'store_name' => 'nullable|string|max:255',
            'store_type' => 'nullable|in:epicerie,superette,mini_market,autre',
            'address' => 'nullable|string',
            'city' => 'nullable|string',
            'region' => 'nullable|string',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors()
            ], 422);
        }

        $user = auth()->user();
        $user->update($request->only([
            'first_name',
            'last_name',
            'email',
            'store_name',
            'store_type',
            'address',
            'city',
            'region',
        ]));

        return response()->json([
            'success' => true,
            'message' => 'Profil mis à jour avec succès',
            'data' => $user
        ]);
    }

    /**
     * Upload document (KYC)
     */
    public function uploadDocument(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'type' => 'required|in:cin,patente,store_photo',
            'file' => 'required|file|mimes:jpg,jpeg,png,pdf|max:5120', // 5MB
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors()
            ], 422);
        }

        // In a real application, you would upload the file to S3 or similar
        $path = $request->file('file')->store('documents', 'public');

        return response()->json([
            'success' => true,
            'message' => 'Document téléchargé avec succès',
            'data' => [
                'type' => $request->type,
                'path' => $path,
            ]
        ]);
    }

    /**
     * Get credit information
     */
    public function creditInfo()
    {
        $user = auth()->user();

        return response()->json([
            'success' => true,
            'data' => [
                'credit_score' => $user->credit_score,
                'credit_limit' => $user->credit_limit,
                'credit_used' => $user->credit_used,
                'credit_available' => $user->credit_available,
                'credit_level' => $user->credit_level,
                'is_eligible' => $user->is_eligible_for_credit,
            ]
        ]);
    }

    /**
     * Get user statistics
     */
    public function statistics()
    {
        $user = auth()->user();

        $totalOrders = $user->orders()->count();
        $totalSpent = $user->orders()->where('payment_status', 'paid')->sum('total_amount');
        $averageBasket = $totalOrders > 0 ? $totalSpent / $totalOrders : 0;
        $lastOrder = $user->orders()->latest()->first();

        return response()->json([
            'success' => true,
            'data' => [
                'total_orders' => $totalOrders,
                'total_spent' => $totalSpent,
                'average_basket' => $averageBasket,
                'last_order_date' => $lastOrder?->created_at,
                'member_since' => $user->created_at,
            ]
        ]);
    }

    /**
     * Get user favorites
     */
    public function favorites()
    {
        $favorites = auth()->user()
            ->favoriteProducts()
            ->with(['category', 'brand', 'images'])
            ->get();

        return response()->json([
            'success' => true,
            'data' => $favorites
        ]);
    }

    /**
     * Toggle product favorite
     */
    public function toggleFavorite($productId)
    {
        $user = auth()->user();

        if ($user->favoriteProducts()->where('product_id', $productId)->exists()) {
            $user->favoriteProducts()->detach($productId);
            $message = 'Produit retiré des favoris';
            $isFavorite = false;
        } else {
            $user->favoriteProducts()->attach($productId);
            $message = 'Produit ajouté aux favoris';
            $isFavorite = true;
        }

        return response()->json([
            'success' => true,
            'message' => $message,
            'is_favorite' => $isFavorite
        ]);
    }
}
