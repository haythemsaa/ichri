<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    public function run(): void
    {
        // Admin user
        $admin = User::create([
            'phone' => '+21612345678',
            'email' => 'admin@ichri.tn',
            'password' => Hash::make('Password123'),
            'first_name' => 'Admin',
            'last_name' => 'ichri.tn',
            'is_verified' => true,
            'is_active' => true,
            'phone_verified_at' => now(),
            'email_verified_at' => now(),
        ]);
        $admin->assignRole('admin');

        // Test grocer users
        $grocers = [
            [
                'phone' => '+21698123456',
                'first_name' => 'Ahmed',
                'last_name' => 'Ben Salah',
                'store_name' => 'Épicerie Essalem',
                'city' => 'Sfax',
                'region' => 'Sfax Ville',
                'address' => 'Rue République, Sfax',
            ],
            [
                'phone' => '+21623456789',
                'first_name' => 'Mohamed',
                'last_name' => 'Trabelsi',
                'store_name' => 'Mini Market Said',
                'city' => 'Tunis',
                'region' => 'Ariana',
                'address' => 'Avenue Habib Bourguiba, Ariana',
            ],
            [
                'phone' => '+21654789123',
                'first_name' => 'Fatma',
                'last_name' => 'Gharbi',
                'store_name' => 'Superette Amine',
                'city' => 'Sousse',
                'region' => 'Sousse Ville',
                'address' => 'Rue Hedi Chaker, Sousse',
            ],
        ];

        foreach ($grocers as $grocerData) {
            $grocerData['password'] = Hash::make('Password123');
            $grocerData['store_type'] = 'epicerie';
            $grocerData['is_verified'] = true;
            $grocerData['is_active'] = true;
            $grocerData['phone_verified_at'] = now();
            $grocerData['credit_score'] = rand(60, 90);
            $grocerData['credit_limit'] = [500, 1500, 5000][rand(0, 2)];
            $grocerData['credit_level'] = ['bronze', 'silver', 'gold'][rand(0, 2)];

            $grocer = User::create($grocerData);
            $grocer->assignRole('grocer');
        }
    }
}
