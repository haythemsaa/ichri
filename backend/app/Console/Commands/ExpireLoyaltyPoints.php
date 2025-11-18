<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Services\LoyaltyService;

class ExpireLoyaltyPoints extends Command
{
    protected $signature = 'loyalty:expire-points';
    protected $description = 'Expire old loyalty points based on expiry configuration';

    public function handle(LoyaltyService $loyaltyService): int
    {
        $this->info('Starting loyalty points expiration...');

        $totalExpired = $loyaltyService->expireOldPoints();

        $this->info("✅ Expired {$totalExpired} points");

        return Command::SUCCESS;
    }
}
