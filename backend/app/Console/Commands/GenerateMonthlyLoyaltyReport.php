<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\LoyaltyTransaction;
use App\Models\LoyaltyRedemption;
use Illuminate\Support\Facades\Mail;

class GenerateMonthlyLoyaltyReport extends Command
{
    protected $signature = 'loyalty:monthly-report {--email=}';
    protected $description = 'Generate monthly loyalty program report';

    public function handle(): int
    {
        $this->info('Generating monthly loyalty report...');

        $lastMonth = now()->subMonth();

        $stats = [
            'points_earned' => LoyaltyTransaction::where('type', 'earn')
                ->whereMonth('created_at', $lastMonth->month)
                ->whereYear('created_at', $lastMonth->year)
                ->sum('points'),

            'points_redeemed' => abs(LoyaltyTransaction::where('type', 'redeem')
                ->whereMonth('created_at', $lastMonth->month)
                ->whereYear('created_at', $lastMonth->year)
                ->sum('points')),

            'redemptions_count' => LoyaltyRedemption::whereMonth('created_at', $lastMonth->month)
                ->whereYear('created_at', $lastMonth->year)
                ->count(),

            'active_users' => LoyaltyTransaction::whereMonth('created_at', $lastMonth->month)
                ->whereYear('created_at', $lastMonth->year)
                ->distinct('user_id')
                ->count('user_id'),
        ];

        $this->table(
            ['Metric', 'Value'],
            [
                ['Points Earned', number_format($stats['points_earned'])],
                ['Points Redeemed', number_format($stats['points_redeemed'])],
                ['Redemptions', $stats['redemptions_count']],
                ['Active Users', $stats['active_users']],
            ]
        );

        $this->info('✅ Report generated successfully');

        return Command::SUCCESS;
    }
}
