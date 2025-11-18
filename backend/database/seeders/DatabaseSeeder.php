<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        $this->command->info('🚀 Starting ichri.tn database seeding...');
        $this->command->newLine();

        $this->call([
            RoleSeeder::class,
            CategorySeeder::class,
            BrandSeeder::class,
            ProductSeeder::class,
            UserSeeder::class,
            DriverSeeder::class,
            PromotionSeeder::class,
            KarnySeeder::class,
            DigitalServiceCommissionSeeder::class,
        ]);

        $this->command->newLine();
        $this->command->info('✅ Database seeding completed successfully!');
        $this->command->info('🇹🇳 ichri.tn is ready to launch! 🚀');
    }
}
