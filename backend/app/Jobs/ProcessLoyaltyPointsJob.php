<?php

namespace App\Jobs;

use App\Models\Order;
use App\Models\LoyaltyPoint;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

class ProcessLoyaltyPointsJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public $tries = 3;
    public $timeout = 60;

    /**
     * Create a new job instance.
     */
    public function __construct(
        public Order $order
    ) {}

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        try {
            $loyaltyPoint = LoyaltyPoint::firstOrCreate(
                ['user_id' => $this->order->user_id],
                [
                    'points' => 0,
                    'lifetime_points' => 0,
                    'tier' => 'bronze',
                ]
            );

            $basePoints = (int) floor($this->order->total_amount);

            $earnedPoints = $loyaltyPoint->addPoints(
                $basePoints,
                'earn',
                'Order',
                $this->order->id,
                "Commande #{$this->order->order_number} - {$this->order->total_amount} TND"
            );

            Log::info("Loyalty points processed via job", [
                'order_id' => $this->order->id,
                'user_id' => $this->order->user_id,
                'points_earned' => $earnedPoints,
            ]);

        } catch (\Exception $e) {
            Log::error("Failed to process loyalty points", [
                'order_id' => $this->order->id,
                'error' => $e->getMessage(),
            ]);

            throw $e; // Will trigger retry
        }
    }
}
