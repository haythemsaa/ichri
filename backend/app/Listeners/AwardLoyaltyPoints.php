<?php

namespace App\Listeners;

use App\Events\OrderCompleted;
use App\Models\LoyaltyPoint;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Support\Facades\Log;

class AwardLoyaltyPoints implements ShouldQueue
{
    use InteractsWithQueue;

    /**
     * The number of times the job may be attempted.
     *
     * @var int
     */
    public $tries = 3;

    /**
     * Handle the event.
     */
    public function handle(OrderCompleted $event): void
    {
        $order = $event->order;

        // Only award points for completed orders
        if ($order->status !== 'completed') {
            return;
        }

        try {
            // Get or create loyalty points for user
            $loyaltyPoint = LoyaltyPoint::firstOrCreate(
                ['user_id' => $order->user_id],
                [
                    'points' => 0,
                    'lifetime_points' => 0,
                    'tier' => 'bronze',
                ]
            );

            // Calculate points: 1 point per TND spent (rounded)
            $basePoints = (int) floor($order->total_amount);

            // Add points (will be multiplied by tier multiplier in the model)
            $earnedPoints = $loyaltyPoint->addPoints(
                $basePoints,
                'earn',
                'Order',
                $order->id,
                "Commande #{$order->id} - {$order->total_amount} TND"
            );

            Log::info("Loyalty points awarded", [
                'user_id' => $order->user_id,
                'order_id' => $order->id,
                'base_points' => $basePoints,
                'earned_points' => $earnedPoints,
                'tier' => $loyaltyPoint->tier,
                'multiplier' => $loyaltyPoint->getTierMultiplier(),
            ]);

        } catch (\Exception $e) {
            Log::error("Failed to award loyalty points", [
                'order_id' => $order->id,
                'user_id' => $order->user_id,
                'error' => $e->getMessage(),
            ]);

            // Don't throw exception - we don't want to fail order completion
            // Points can be manually awarded later
        }
    }
}
