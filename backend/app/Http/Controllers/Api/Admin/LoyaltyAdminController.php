<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use App\Models\LoyaltyReward;
use App\Models\LoyaltyRedemption;
use App\Models\LoyaltyPoint;
use App\Models\LoyaltyTransaction;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\DB;

class LoyaltyAdminController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth:api');
        // Add admin middleware check if available
        // $this->middleware('role:admin');
    }

    /**
     * Get all rewards (admin view)
     */
    public function getRewards(Request $request)
    {
        $perPage = $request->input('per_page', 20);
        $status = $request->input('status'); // all, active, inactive

        $query = LoyaltyReward::query();

        if ($status === 'active') {
            $query->where('is_active', true);
        } elseif ($status === 'inactive') {
            $query->where('is_active', false);
        }

        $rewards = $query->orderBy('points_cost', 'asc')->paginate($perPage);

        return response()->json([
            'success' => true,
            'data' => $rewards
        ]);
    }

    /**
     * Create new reward
     */
    public function createReward(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'type' => 'required|in:discount,product,cashback,free_delivery',
            'points_cost' => 'required|integer|min:1',
            'config' => 'nullable|array',
            'quantity_available' => 'nullable|integer|min:0',
            'is_active' => 'boolean',
            'valid_from' => 'nullable|date',
            'valid_until' => 'nullable|date|after:valid_from',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors()
            ], 422);
        }

        $reward = LoyaltyReward::create($request->all());

        return response()->json([
            'success' => true,
            'message' => 'Récompense créée avec succès',
            'data' => $reward
        ], 201);
    }

    /**
     * Update reward
     */
    public function updateReward(Request $request, $id)
    {
        $reward = LoyaltyReward::findOrFail($id);

        $validator = Validator::make($request->all(), [
            'name' => 'string|max:255',
            'description' => 'nullable|string',
            'type' => 'in:discount,product,cashback,free_delivery',
            'points_cost' => 'integer|min:1',
            'config' => 'nullable|array',
            'quantity_available' => 'nullable|integer|min:0',
            'is_active' => 'boolean',
            'valid_from' => 'nullable|date',
            'valid_until' => 'nullable|date|after:valid_from',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors()
            ], 422);
        }

        $reward->update($request->all());

        return response()->json([
            'success' => true,
            'message' => 'Récompense mise à jour',
            'data' => $reward->fresh()
        ]);
    }

    /**
     * Delete reward
     */
    public function deleteReward($id)
    {
        $reward = LoyaltyReward::findOrFail($id);

        // Check if reward has active redemptions
        $activeRedemptions = LoyaltyRedemption::where('reward_id', $id)
            ->whereIn('status', ['pending', 'approved'])
            ->count();

        if ($activeRedemptions > 0) {
            return response()->json([
                'success' => false,
                'message' => "Impossible de supprimer. {$activeRedemptions} échanges actifs."
            ], 400);
        }

        $reward->delete();

        return response()->json([
            'success' => true,
            'message' => 'Récompense supprimée'
        ]);
    }

    /**
     * Get all redemptions (admin view)
     */
    public function getRedemptions(Request $request)
    {
        $perPage = $request->input('per_page', 20);
        $status = $request->input('status');
        $userId = $request->input('user_id');

        $query = LoyaltyRedemption::with(['user', 'reward'])
            ->orderByDesc('created_at');

        if ($status) {
            $query->where('status', $status);
        }

        if ($userId) {
            $query->where('user_id', $userId);
        }

        $redemptions = $query->paginate($perPage);

        return response()->json([
            'success' => true,
            'data' => $redemptions
        ]);
    }

    /**
     * Approve redemption
     */
    public function approveRedemption($id)
    {
        $redemption = LoyaltyRedemption::findOrFail($id);

        if ($redemption->status !== 'pending') {
            return response()->json([
                'success' => false,
                'message' => 'Seuls les échanges en attente peuvent être approuvés'
            ], 400);
        }

        $redemption->approve();

        return response()->json([
            'success' => true,
            'message' => 'Échange approuvé',
            'data' => $redemption->fresh()
        ]);
    }

    /**
     * Get loyalty statistics
     */
    public function getStatistics()
    {
        $stats = [
            'total_users_with_points' => LoyaltyPoint::where('points', '>', 0)->count(),
            'total_points_issued' => LoyaltyPoint::sum('lifetime_points'),
            'total_points_available' => LoyaltyPoint::sum('points'),
            'total_points_redeemed' => LoyaltyPoint::sum('lifetime_points') - LoyaltyPoint::sum('points'),

            'tier_distribution' => [
                'bronze' => LoyaltyPoint::where('tier', 'bronze')->count(),
                'silver' => LoyaltyPoint::where('tier', 'silver')->count(),
                'gold' => LoyaltyPoint::where('tier', 'gold')->count(),
                'platinum' => LoyaltyPoint::where('tier', 'platinum')->count(),
            ],

            'redemptions' => [
                'total' => LoyaltyRedemption::count(),
                'pending' => LoyaltyRedemption::where('status', 'pending')->count(),
                'approved' => LoyaltyRedemption::where('status', 'approved')->count(),
                'used' => LoyaltyRedemption::where('status', 'used')->count(),
                'cancelled' => LoyaltyRedemption::where('status', 'cancelled')->count(),
            ],

            'rewards' => [
                'total' => LoyaltyReward::count(),
                'active' => LoyaltyReward::where('is_active', true)->count(),
                'inactive' => LoyaltyReward::where('is_active', false)->count(),
            ],

            'transactions_this_month' => LoyaltyTransaction::whereMonth('created_at', now()->month)->count(),
            'points_earned_this_month' => LoyaltyTransaction::whereMonth('created_at', now()->month)
                ->where('type', 'earn')
                ->sum('points'),
            'points_redeemed_this_month' => abs(LoyaltyTransaction::whereMonth('created_at', now()->month)
                ->where('type', 'redeem')
                ->sum('points')),
        ];

        return response()->json([
            'success' => true,
            'data' => $stats
        ]);
    }

    /**
     * Get top users by points
     */
    public function getTopUsers(Request $request)
    {
        $limit = $request->input('limit', 10);
        $type = $request->input('type', 'lifetime'); // current or lifetime

        $column = $type === 'lifetime' ? 'lifetime_points' : 'points';

        $topUsers = LoyaltyPoint::with('user')
            ->orderByDesc($column)
            ->limit($limit)
            ->get()
            ->map(function ($loyaltyPoint) use ($column) {
                return [
                    'user' => $loyaltyPoint->user,
                    'points' => $loyaltyPoint->$column,
                    'tier' => $loyaltyPoint->tier,
                    'tier_multiplier' => $loyaltyPoint->getTierMultiplier(),
                ];
            });

        return response()->json([
            'success' => true,
            'data' => $topUsers
        ]);
    }

    /**
     * Manually adjust user points (admin only)
     */
    public function adjustPoints(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'user_id' => 'required|exists:users,id',
            'points' => 'required|integer',
            'type' => 'required|in:bonus,adjustment,expire',
            'reason' => 'required|string',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors()
            ], 422);
        }

        $loyaltyPoint = LoyaltyPoint::firstOrCreate(
            ['user_id' => $request->user_id],
            [
                'points' => 0,
                'lifetime_points' => 0,
                'tier' => 'bronze',
            ]
        );

        if ($request->points > 0) {
            $loyaltyPoint->addPoints(
                $request->points,
                $request->type,
                'Admin',
                auth()->id(),
                $request->reason
            );
        } else {
            $loyaltyPoint->redeemPoints(
                abs($request->points),
                null,
                $request->reason
            );
        }

        return response()->json([
            'success' => true,
            'message' => 'Points ajustés avec succès',
            'data' => [
                'new_balance' => $loyaltyPoint->fresh()->points,
                'tier' => $loyaltyPoint->tier,
            ]
        ]);
    }

    /**
     * Get reward redemption analytics
     */
    public function getRewardAnalytics()
    {
        $popularRewards = DB::table('loyalty_redemptions')
            ->select('reward_id', DB::raw('COUNT(*) as redemption_count'), DB::raw('SUM(points_spent) as total_points_spent'))
            ->join('loyalty_rewards', 'loyalty_redemptions.reward_id', '=', 'loyalty_rewards.id')
            ->groupBy('reward_id')
            ->orderByDesc('redemption_count')
            ->limit(10)
            ->get();

        $rewardDetails = [];
        foreach ($popularRewards as $stat) {
            $reward = LoyaltyReward::find($stat->reward_id);
            $rewardDetails[] = [
                'reward' => $reward,
                'redemption_count' => $stat->redemption_count,
                'total_points_spent' => $stat->total_points_spent,
            ];
        }

        return response()->json([
            'success' => true,
            'data' => [
                'popular_rewards' => $rewardDetails,
                'redemptions_by_month' => DB::table('loyalty_redemptions')
                    ->select(DB::raw('DATE_FORMAT(created_at, "%Y-%m") as month'), DB::raw('COUNT(*) as count'))
                    ->where('created_at', '>=', now()->subMonths(12))
                    ->groupBy('month')
                    ->orderBy('month', 'asc')
                    ->get(),
            ]
        ]);
    }
}
