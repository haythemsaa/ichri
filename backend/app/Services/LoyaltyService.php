<?php

namespace App\Services;

use App\Models\LoyaltyPoint;
use App\Models\LoyaltyReward;
use App\Models\LoyaltyRedemption;
use App\Models\Order;
use App\Notifications\LoyaltyPointsEarnedNotification;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class LoyaltyService
{
    /**
     * Award points for an order
     */
    public function awardPointsForOrder(Order $order): int
    {
        $loyaltyPoint = LoyaltyPoint::firstOrCreate(
            ['user_id' => $order->user_id],
            [
                'points' => 0,
                'lifetime_points' => 0,
                'tier' => 'bronze',
            ]
        );

        $basePoints = (int) floor($order->total_amount);
        $earnedPoints = $loyaltyPoint->addPoints(
            $basePoints,
            'earn',
            'Order',
            $order->id,
            "Commande #{$order->order_number} - {$order->total_amount} TND"
        );

        // Send notification
        $order->user->notify(new LoyaltyPointsEarnedNotification(
            $earnedPoints,
            $loyaltyPoint->points,
            $loyaltyPoint->tier,
            $order->order_number
        ));

        Log::info("Loyalty points awarded", [
            'order_id' => $order->id,
            'user_id' => $order->user_id,
            'points_earned' => $earnedPoints,
            'tier' => $loyaltyPoint->tier,
        ]);

        return $earnedPoints;
    }

    /**
     * Redeem a reward
     */
    public function redeemReward(int $userId, int $rewardId): LoyaltyRedemption
    {
        return DB::transaction(function () use ($userId, $rewardId) {
            $reward = LoyaltyReward::lockForUpdate()->findOrFail($rewardId);

            if (!$reward->isAvailable()) {
                throw new \Exception('Cette récompense n\'est pas disponible');
            }

            $loyaltyPoint = LoyaltyPoint::lockForUpdate()
                ->where('user_id', $userId)
                ->firstOrFail();

            if ($loyaltyPoint->points < $reward->points_cost) {
                throw new \Exception('Points insuffisants');
            }

            // Deduct points
            $loyaltyPoint->redeemPoints($reward->points_cost, $reward->id, 'Redeemed: ' . $reward->name);

            // Create redemption
            $redemption = LoyaltyRedemption::create([
                'user_id' => $userId,
                'reward_id' => $reward->id,
                'points_spent' => $reward->points_cost,
                'status' => 'approved',
            ]);

            // Decrease quantity
            $reward->decrementQuantity();

            return $redemption;
        });
    }

    /**
     * Get user tier progress
     */
    public function getTierProgress(int $userId): array
    {
        $loyaltyPoint = LoyaltyPoint::where('user_id', $userId)->first();

        if (!$loyaltyPoint) {
            return [
                'current_tier' => 'bronze',
                'next_tier' => 'silver',
                'progress_percentage' => 0,
                'points_to_next_tier' => 1000,
            ];
        }

        $tiers = ['bronze' => 0, 'silver' => 1000, 'gold' => 5000, 'platinum' => 15000];
        $currentTier = $loyaltyPoint->tier;
        $tierOrder = array_keys($tiers);
        $currentIndex = array_search($currentTier, $tierOrder);

        if ($currentIndex === count($tierOrder) - 1) {
            return [
                'current_tier' => $currentTier,
                'next_tier' => null,
                'progress_percentage' => 100,
                'points_to_next_tier' => 0,
            ];
        }

        $nextTier = $tierOrder[$currentIndex + 1];
        $currentThreshold = $tiers[$currentTier];
        $nextThreshold = $tiers[$nextTier];
        $pointsInTier = $loyaltyPoint->lifetime_points - $currentThreshold;
        $tierRange = $nextThreshold - $currentThreshold;
        $progressPercentage = ($pointsInTier / $tierRange) * 100;

        return [
            'current_tier' => $currentTier,
            'next_tier' => $nextTier,
            'progress_percentage' => min(100, $progressPercentage),
            'points_to_next_tier' => max(0, $nextThreshold - $loyaltyPoint->lifetime_points),
        ];
    }

    /**
     * Expire old points
     */
    public function expireOldPoints(): int
    {
        $expiryDays = config('loyalty.point_expiry.days', 365);
        $expiryDate = now()->subDays($expiryDays);

        $expiredTransactions = \App\Models\LoyaltyTransaction::where('type', 'earn')
            ->where('created_at', '<', $expiryDate')
            ->whereNull('expired_at')
            ->get();

        $totalExpired = 0;

        foreach ($expiredTransactions as $transaction) {
            $loyaltyPoint = LoyaltyPoint::find($transaction->user_id);
            if ($loyaltyPoint && $loyaltyPoint->points >= $transaction->points) {
                $loyaltyPoint->decrement('points', $transaction->points);
                $transaction->update(['expired_at' => now()]);
                $totalExpired += $transaction->points;
            }
        }

        return $totalExpired;
    }
}
