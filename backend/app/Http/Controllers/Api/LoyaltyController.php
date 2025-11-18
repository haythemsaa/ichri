<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\LoyaltyPoint;
use App\Models\LoyaltyReward;
use App\Models\LoyaltyRedemption;
use App\Models\LoyaltyTransaction;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class LoyaltyController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth:api');
    }

    /**
     * Get user's loyalty points and tier info
     */
    public function getPoints()
    {
        $loyalty = LoyaltyPoint::firstOrCreate(
            ['user_id' => auth()->id()],
            [
                'points' => 0,
                'lifetime_points' => 0,
                'tier' => 'bronze',
            ]
        );

        return response()->json([
            'success' => true,
            'data' => [
                'points' => $loyalty->points,
                'lifetime_points' => $loyalty->lifetime_points,
                'tier' => $loyalty->tier,
                'tier_multiplier' => $loyalty->getTierMultiplier(),
                'tier_expires_at' => $loyalty->tier_expires_at,
                'next_tier' => $this->getNextTier($loyalty->tier),
                'points_to_next_tier' => $this->getPointsToNextTier($loyalty),
            ]
        ]);
    }

    /**
     * Get loyalty transactions history
     */
    public function getTransactions(Request $request)
    {
        $perPage = $request->input('per_page', 20);
        $type = $request->input('type'); // earn, redeem, expire, bonus, adjustment

        $query = LoyaltyTransaction::where('user_id', auth()->id())
            ->orderByDesc('created_at');

        if ($type) {
            $query->where('type', $type);
        }

        $transactions = $query->paginate($perPage);

        return response()->json([
            'success' => true,
            'data' => $transactions
        ]);
    }

    /**
     * Get all available rewards
     */
    public function getRewards(Request $request)
    {
        $type = $request->input('type'); // discount, product, cashback, free_delivery

        $query = LoyaltyReward::available();

        if ($type) {
            $query->where('type', $type);
        }

        $rewards = $query->orderBy('points_cost', 'asc')->get();

        $loyaltyPoints = LoyaltyPoint::where('user_id', auth()->id())->first();
        $userPoints = $loyaltyPoints ? $loyaltyPoints->points : 0;

        // Add affordability info to each reward
        $rewards = $rewards->map(function ($reward) use ($userPoints) {
            $reward->can_afford = $userPoints >= $reward->points_cost;
            $reward->points_needed = max(0, $reward->points_cost - $userPoints);
            return $reward;
        });

        return response()->json([
            'success' => true,
            'data' => [
                'rewards' => $rewards,
                'user_points' => $userPoints,
            ]
        ]);
    }

    /**
     * Get single reward details
     */
    public function getReward($id)
    {
        $reward = LoyaltyReward::findOrFail($id);

        if (!$reward->isAvailable()) {
            return response()->json([
                'success' => false,
                'message' => 'Cette récompense n\'est pas disponible'
            ], 404);
        }

        $loyaltyPoints = LoyaltyPoint::where('user_id', auth()->id())->first();
        $userPoints = $loyaltyPoints ? $loyaltyPoints->points : 0;

        $reward->can_afford = $userPoints >= $reward->points_cost;
        $reward->points_needed = max(0, $reward->points_cost - $userPoints);

        return response()->json([
            'success' => true,
            'data' => $reward
        ]);
    }

    /**
     * Redeem a reward
     */
    public function redeemReward(Request $request, $id)
    {
        $reward = LoyaltyReward::findOrFail($id);

        if (!$reward->isAvailable()) {
            return response()->json([
                'success' => false,
                'message' => 'Cette récompense n\'est pas disponible'
            ], 400);
        }

        $loyaltyPoint = LoyaltyPoint::where('user_id', auth()->id())->first();

        if (!$loyaltyPoint || $loyaltyPoint->points < $reward->points_cost) {
            return response()->json([
                'success' => false,
                'message' => 'Points insuffisants',
                'data' => [
                    'required' => $reward->points_cost,
                    'available' => $loyaltyPoint ? $loyaltyPoint->points : 0,
                ]
            ], 400);
        }

        try {
            // Deduct points
            $loyaltyPoint->redeemPoints($reward->points_cost, $reward->id, 'Redeemed: ' . $reward->name);

            // Create redemption
            $redemption = LoyaltyRedemption::create([
                'user_id' => auth()->id(),
                'reward_id' => $reward->id,
                'points_spent' => $reward->points_cost,
                'status' => 'approved', // Auto-approve for now
            ]);

            // Decrease reward quantity
            $reward->decrementQuantity();

            return response()->json([
                'success' => true,
                'message' => 'Récompense échangée avec succès!',
                'data' => [
                    'redemption' => $redemption,
                    'remaining_points' => $loyaltyPoint->fresh()->points,
                ]
            ], 201);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Erreur lors de l\'échange: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get user's redemptions
     */
    public function getRedemptions(Request $request)
    {
        $perPage = $request->input('per_page', 20);
        $status = $request->input('status'); // pending, approved, used, expired, cancelled

        $query = LoyaltyRedemption::where('user_id', auth()->id())
            ->with('reward')
            ->orderByDesc('created_at');

        if ($status) {
            $query->where('status', $status);
        }

        $redemptions = $query->paginate($perPage);

        return response()->json([
            'success' => true,
            'data' => $redemptions
        ]);
    }

    /**
     * Get single redemption details
     */
    public function getRedemption($id)
    {
        $redemption = LoyaltyRedemption::where('user_id', auth()->id())
            ->with('reward')
            ->findOrFail($id);

        return response()->json([
            'success' => true,
            'data' => $redemption
        ]);
    }

    /**
     * Use a redemption code
     */
    public function useRedemption(Request $request, $id)
    {
        $redemption = LoyaltyRedemption::where('user_id', auth()->id())->findOrFail($id);

        if (!$redemption->isUsable()) {
            return response()->json([
                'success' => false,
                'message' => 'Ce code ne peut pas être utilisé (déjà utilisé ou expiré)'
            ], 400);
        }

        try {
            $redemption->use();

            return response()->json([
                'success' => true,
                'message' => 'Code utilisé avec succès',
                'data' => $redemption->fresh()
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
            ], 400);
        }
    }

    /**
     * Cancel a redemption
     */
    public function cancelRedemption($id)
    {
        $redemption = LoyaltyRedemption::where('user_id', auth()->id())->findOrFail($id);

        try {
            $redemption->cancel();

            return response()->json([
                'success' => true,
                'message' => 'Échange annulé et points remboursés',
                'data' => $redemption->fresh()
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
            ], 400);
        }
    }

    /**
     * Get tier information
     */
    public function getTiers()
    {
        $tiers = [
            [
                'name' => 'bronze',
                'threshold' => LoyaltyPoint::TIER_THRESHOLDS['bronze'],
                'multiplier' => LoyaltyPoint::TIER_MULTIPLIERS['bronze'],
                'benefits' => ['Points de base'],
            ],
            [
                'name' => 'silver',
                'threshold' => LoyaltyPoint::TIER_THRESHOLDS['silver'],
                'multiplier' => LoyaltyPoint::TIER_MULTIPLIERS['silver'],
                'benefits' => ['+20% de points sur chaque commande', 'Livraison prioritaire'],
            ],
            [
                'name' => 'gold',
                'threshold' => LoyaltyPoint::TIER_THRESHOLDS['gold'],
                'multiplier' => LoyaltyPoint::TIER_MULTIPLIERS['gold'],
                'benefits' => ['+50% de points', 'Support prioritaire', 'Offres exclusives'],
            ],
            [
                'name' => 'platinum',
                'threshold' => LoyaltyPoint::TIER_THRESHOLDS['platinum'],
                'multiplier' => LoyaltyPoint::TIER_MULTIPLIERS['platinum'],
                'benefits' => ['Double points (2x)', 'Account manager dédié', 'Offres VIP', 'Livraison gratuite'],
            ],
        ];

        $loyaltyPoint = LoyaltyPoint::where('user_id', auth()->id())->first();
        $currentTier = $loyaltyPoint ? $loyaltyPoint->tier : 'bronze';
        $lifetimePoints = $loyaltyPoint ? $loyaltyPoint->lifetime_points : 0;

        return response()->json([
            'success' => true,
            'data' => [
                'tiers' => $tiers,
                'current_tier' => $currentTier,
                'lifetime_points' => $lifetimePoints,
            ]
        ]);
    }

    // Helper methods
    private function getNextTier($currentTier)
    {
        $tierOrder = ['bronze', 'silver', 'gold', 'platinum'];
        $currentIndex = array_search($currentTier, $tierOrder);

        if ($currentIndex === false || $currentIndex === count($tierOrder) - 1) {
            return null; // Already at max tier
        }

        return $tierOrder[$currentIndex + 1];
    }

    private function getPointsToNextTier($loyaltyPoint)
    {
        $nextTier = $this->getNextTier($loyaltyPoint->tier);

        if (!$nextTier) {
            return 0; // Already at max tier
        }

        $nextThreshold = LoyaltyPoint::TIER_THRESHOLDS[$nextTier];
        return max(0, $nextThreshold - $loyaltyPoint->lifetime_points);
    }
}
