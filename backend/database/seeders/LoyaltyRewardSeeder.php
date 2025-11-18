<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\LoyaltyReward;

class LoyaltyRewardSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $rewards = [
            // Free Delivery Rewards
            [
                'name' => 'Livraison Gratuite',
                'description' => 'Profitez d\'une livraison gratuite sur votre prochaine commande',
                'type' => 'free_delivery',
                'points_cost' => 200,
                'config' => json_encode(['min_order_amount' => 0]),
                'quantity_available' => null, // Unlimited
                'is_active' => true,
                'valid_from' => null,
                'valid_until' => null,
            ],

            // Discount Rewards
            [
                'name' => 'Réduction 5 TND',
                'description' => 'Obtenez 5 TND de réduction sur votre prochaine commande',
                'type' => 'discount',
                'points_cost' => 250,
                'config' => json_encode(['amount' => 5, 'min_order_amount' => 50]),
                'quantity_available' => null,
                'is_active' => true,
                'valid_from' => null,
                'valid_until' => null,
            ],
            [
                'name' => 'Réduction 10 TND',
                'description' => 'Obtenez 10 TND de réduction sur votre prochaine commande',
                'type' => 'discount',
                'points_cost' => 500,
                'config' => json_encode(['amount' => 10, 'min_order_amount' => 100]),
                'quantity_available' => null,
                'is_active' => true,
                'valid_from' => null,
                'valid_until' => null,
            ],
            [
                'name' => 'Réduction 25 TND',
                'description' => 'Obtenez 25 TND de réduction sur votre prochaine commande',
                'type' => 'discount',
                'points_cost' => 1200,
                'config' => json_encode(['amount' => 25, 'min_order_amount' => 200]),
                'quantity_available' => null,
                'is_active' => true,
                'valid_from' => null,
                'valid_until' => null,
            ],
            [
                'name' => 'Réduction 50 TND',
                'description' => 'Obtenez 50 TND de réduction sur votre prochaine commande',
                'type' => 'discount',
                'points_cost' => 2500,
                'config' => json_encode(['amount' => 50, 'min_order_amount' => 500]),
                'quantity_available' => null,
                'is_active' => true,
                'valid_from' => null,
                'valid_until' => null,
            ],

            // Percentage Discounts
            [
                'name' => 'Réduction 5%',
                'description' => 'Obtenez 5% de réduction sur votre prochaine commande',
                'type' => 'discount',
                'points_cost' => 400,
                'config' => json_encode(['percentage' => 5, 'max_discount' => 50]),
                'quantity_available' => null,
                'is_active' => true,
                'valid_from' => null,
                'valid_until' => null,
            ],
            [
                'name' => 'Réduction 10%',
                'description' => 'Obtenez 10% de réduction sur votre prochaine commande',
                'type' => 'discount',
                'points_cost' => 800,
                'config' => json_encode(['percentage' => 10, 'max_discount' => 100]),
                'quantity_available' => null,
                'is_active' => true,
                'valid_from' => null,
                'valid_until' => null,
            ],
            [
                'name' => 'Réduction 15%',
                'description' => 'Obtenez 15% de réduction sur votre prochaine commande',
                'type' => 'discount',
                'points_cost' => 1500,
                'config' => json_encode(['percentage' => 15, 'max_discount' => 150]),
                'quantity_available' => null,
                'is_active' => true,
                'valid_from' => null,
                'valid_until' => null,
            ],

            // Cashback Rewards
            [
                'name' => 'Cashback 20 TND',
                'description' => 'Recevez 20 TND de crédit sur votre compte Karny',
                'type' => 'cashback',
                'points_cost' => 1000,
                'config' => json_encode(['amount' => 20]),
                'quantity_available' => null,
                'is_active' => true,
                'valid_from' => null,
                'valid_until' => null,
            ],
            [
                'name' => 'Cashback 50 TND',
                'description' => 'Recevez 50 TND de crédit sur votre compte Karny',
                'type' => 'cashback',
                'points_cost' => 2500,
                'config' => json_encode(['amount' => 50]),
                'quantity_available' => null,
                'is_active' => true,
                'valid_from' => null,
                'valid_until' => null,
            ],

            // Product Rewards (Examples)
            [
                'name' => 'Produit Gratuit - Café',
                'description' => 'Un paquet de café gratuit (1kg) avec votre prochaine commande',
                'type' => 'product',
                'points_cost' => 600,
                'config' => json_encode(['product_id' => null, 'category' => 'cafe']),
                'quantity_available' => 50,
                'is_active' => true,
                'valid_from' => null,
                'valid_until' => now()->addMonths(3),
            ],
            [
                'name' => 'Produit Gratuit - Huile',
                'description' => 'Une bouteille d\'huile gratuite (1L) avec votre prochaine commande',
                'type' => 'product',
                'points_cost' => 800,
                'config' => json_encode(['product_id' => null, 'category' => 'huile']),
                'quantity_available' => 30,
                'is_active' => true,
                'valid_from' => null,
                'valid_until' => now()->addMonths(3),
            ],

            // VIP Rewards (High tier)
            [
                'name' => 'Livraison Prioritaire - 1 Mois',
                'description' => 'Livraison prioritaire garantie pour toutes vos commandes pendant 1 mois',
                'type' => 'product',
                'points_cost' => 3000,
                'config' => json_encode(['duration_days' => 30, 'type' => 'priority_delivery'}),
                'quantity_available' => 20,
                'is_active' => true,
                'valid_from' => null,
                'valid_until' => null,
            ],
            [
                'name' => 'Account Manager Dédié',
                'description' => 'Profitez d\'un account manager dédié pendant 3 mois',
                'type' => 'product',
                'points_cost' => 5000,
                'config' => json_encode(['duration_days' => 90, 'type' => 'account_manager'}),
                'quantity_available' => 10,
                'is_active' => true,
                'valid_from' => null,
                'valid_until' => null,
            ],

            // Seasonal/Limited Rewards
            [
                'name' => 'Pack Premium Ramadan',
                'description' => 'Pack de produits premium pour le Ramadan',
                'type' => 'product',
                'points_cost' => 2000,
                'config' => json_encode(['seasonal' => true, 'season' => 'ramadan'}),
                'quantity_available' => 100,
                'is_active' => false, // Activate during Ramadan
                'valid_from' => null,
                'valid_until' => null,
            ],
        ];

        foreach ($rewards as $reward) {
            LoyaltyReward::create($reward);
        }

        $this->command->info('✅ Loyalty rewards seeded successfully! (' . count($rewards) . ' rewards)');
    }
}
