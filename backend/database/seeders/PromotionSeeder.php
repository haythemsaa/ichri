<?php

namespace Database\Seeders;

use App\Models\Promotion;
use App\Models\Product;
use App\Models\Category;
use Illuminate\Database\Seeder;

class PromotionSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $this->command->info('🎁 Seeding promotions...');

        // 1. Promotion Ramadan 2024 (percentage sur catégories)
        $ramadan = Promotion::create([
            'name' => 'Ramadan Kareem 2024',
            'code' => 'RAMADAN2024',
            'description' => '-15% sur tous les produits laitiers et boissons pendant le Ramadan',
            'type' => 'percentage',
            'discount_value' => 15,
            'min_purchase' => 50,
            'start_date' => now()->subDays(7),
            'end_date' => now()->addDays(23),
            'is_active' => true,
            'is_featured' => true,
            'terms' => 'Valable sur tous les produits laitiers et boissons. Minimum d\'achat 50 TND.',
        ]);

        // Attach to categories
        $categories = Category::whereIn('slug', ['produits-laitiers', 'boissons'])->pluck('id');
        if ($categories->isNotEmpty()) {
            $ramadan->categories()->attach($categories);
        }

        // 2. Livraison gratuite
        Promotion::create([
            'name' => 'Livraison Gratuite',
            'code' => 'FREESHIP100',
            'description' => 'Livraison gratuite pour toute commande supérieure à 100 TND',
            'type' => 'free_delivery',
            'discount_value' => 0,
            'min_purchase' => 100,
            'start_date' => now()->subDays(30),
            'end_date' => now()->addDays(60),
            'is_active' => true,
            'is_featured' => true,
            'terms' => 'Livraison gratuite automatique sur commandes > 100 TND',
        ]);

        // 3. Nouvelle année promo (fixed amount)
        Promotion::create([
            'name' => 'Nouvelle Année 2025',
            'code' => 'NEWYEAR25',
            'description' => '-25 TND sur votre première commande de l\'année',
            'type' => 'fixed_amount',
            'discount_value' => 25,
            'min_purchase' => 80,
            'usage_limit_per_user' => 1,
            'usage_limit_total' => 500,
            'start_date' => now(),
            'end_date' => now()->addDays(45),
            'is_active' => true,
            'is_featured' => false,
            'terms' => 'Une seule utilisation par utilisateur. 500 utilisations maximum.',
        ]);

        // 4. BOGO Coca-Cola
        $bogo = Promotion::create([
            'name' => 'Coca-Cola 1+1 Gratuit',
            'code' => null,
            'description' => 'Achetez une bouteille de Coca-Cola, recevez-en une gratuitement',
            'type' => 'bogo',
            'discount_value' => 0,
            'min_purchase' => null,
            'start_date' => now()->subDays(5),
            'end_date' => now()->addDays(25),
            'is_active' => true,
            'is_featured' => true,
            'terms' => 'Offre valable sur tous les produits Coca-Cola en stock',
        ]);

        // Attach to Coca-Cola products
        $cocaProducts = Product::where('name', 'LIKE', '%Coca%')->pluck('id');
        if ($cocaProducts->isNotEmpty()) {
            $bogo->products()->attach($cocaProducts);
        }

        // 5. Bundle 2+1 sur huile
        $bundle = Promotion::create([
            'name' => 'Huile 2+1 Gratuit',
            'code' => null,
            'description' => 'Achetez 2 bouteilles d\'huile, recevez-en 1 gratuitement',
            'type' => 'bundle',
            'config' => ['buy' => 2, 'get' => 1],
            'discount_value' => 0,
            'start_date' => now()->subDays(10),
            'end_date' => now()->addDays(20),
            'is_active' => true,
            'is_featured' => true,
            'terms' => 'Achetez 2, recevez 1 gratuit sur toutes les huiles',
        ]);

        // Attach to oil products
        $oilProducts = Product::where('name', 'LIKE', '%huile%')->pluck('id');
        if ($oilProducts->isNotEmpty()) {
            $bundle->products()->attach($oilProducts);
        }

        // 6. Black Friday (percentage générale)
        Promotion::create([
            'name' => 'Black Friday Tunisie',
            'code' => 'BLACKFRI30',
            'description' => '-30% sur toute la boutique pour le Black Friday',
            'type' => 'percentage',
            'discount_value' => 30,
            'min_purchase' => 70,
            'usage_limit_per_user' => 2,
            'usage_limit_total' => 1000,
            'start_date' => now()->addDays(50),
            'end_date' => now()->addDays(53),
            'is_active' => false, // Not active yet
            'is_featured' => false,
            'terms' => 'Offre limitée. Maximum 2 utilisations par client.',
        ]);

        // 7. Fidélité clients (percentage)
        Promotion::create([
            'name' => 'Clients Fidèles',
            'code' => 'VIP10',
            'description' => '-10% pour nos clients fidèles',
            'type' => 'percentage',
            'discount_value' => 10,
            'min_purchase' => 30,
            'start_date' => now()->subDays(60),
            'end_date' => now()->addDays(300),
            'is_active' => true,
            'is_featured' => false,
            'terms' => 'Réservé aux clients avec plus de 5 commandes',
        ]);

        $this->command->info('✅ Created 7 promotions');
    }
}
