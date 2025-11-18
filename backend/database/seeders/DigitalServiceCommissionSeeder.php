<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class DigitalServiceCommissionSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $this->command->info('💰 Seeding digital service commissions...');

        $commissions = [
            // Mobile Top-up - 3% commission
            [
                'service_type' => 'mobile_topup',
                'provider' => 'ooredoo',
                'commission_percentage' => 3.00,
                'commission_fixed' => null,
                'min_commission' => 0.150,
                'max_commission' => 5.000,
            ],
            [
                'service_type' => 'mobile_topup',
                'provider' => 'orange',
                'commission_percentage' => 3.00,
                'commission_fixed' => null,
                'min_commission' => 0.150,
                'max_commission' => 5.000,
            ],
            [
                'service_type' => 'mobile_topup',
                'provider' => 'tunisie_telecom',
                'commission_percentage' => 3.00,
                'commission_fixed' => null,
                'min_commission' => 0.150,
                'max_commission' => 5.000,
            ],

            // Electricity Bills - 1.5% commission
            [
                'service_type' => 'electricity_bill',
                'provider' => 'steg',
                'commission_percentage' => 1.50,
                'commission_fixed' => null,
                'min_commission' => 0.300,
                'max_commission' => 10.000,
            ],

            // Water Bills - 1.5% commission
            [
                'service_type' => 'water_bill',
                'provider' => 'sonede',
                'commission_percentage' => 1.50,
                'commission_fixed' => null,
                'min_commission' => 0.200,
                'max_commission' => 8.000,
            ],

            // Internet Bills - 2% commission
            [
                'service_type' => 'internet_bill',
                'provider' => 'topnet',
                'commission_percentage' => 2.00,
                'commission_fixed' => null,
                'min_commission' => 0.500,
                'max_commission' => 6.000,
            ],
            [
                'service_type' => 'internet_bill',
                'provider' => 'globalnet',
                'commission_percentage' => 2.00,
                'commission_fixed' => null,
                'min_commission' => 0.500,
                'max_commission' => 6.000,
            ],
            [
                'service_type' => 'internet_bill',
                'provider' => 'tunisie_telecom',
                'commission_percentage' => 2.00,
                'commission_fixed' => null,
                'min_commission' => 0.500,
                'max_commission' => 6.000,
            ],

            // Phone Bills - 1.5% commission
            [
                'service_type' => 'phone_bill',
                'provider' => 'tunisie_telecom',
                'commission_percentage' => 1.50,
                'commission_fixed' => null,
                'min_commission' => 0.300,
                'max_commission' => 5.000,
            ],
        ];

        foreach ($commissions as $commission) {
            DB::table('digital_service_commissions')->insert(array_merge($commission, [
                'created_at' => now(),
                'updated_at' => now(),
            ]));
        }

        $this->command->info('✅ Created ' . count($commissions) . ' digital service commission rates');
    }
}
