<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Driver;

class DriverSeeder extends Seeder
{
    public function run(): void
    {
        $drivers = [
            [
                'first_name' => 'Karim',
                'last_name' => 'Belhaj',
                'phone' => '+21699111222',
                'license_number' => 'TN123456',
                'vehicle_type' => 'Van',
                'vehicle_plate' => '123 TU 4567',
                'is_active' => true,
                'is_available' => true,
            ],
            [
                'first_name' => 'Samir',
                'last_name' => 'Jebali',
                'phone' => '+21699222333',
                'license_number' => 'TN234567',
                'vehicle_type' => 'Pickup',
                'vehicle_plate' => '234 TU 5678',
                'is_active' => true,
                'is_available' => true,
            ],
            [
                'first_name' => 'Riadh',
                'last_name' => 'Mansouri',
                'phone' => '+21699333444',
                'license_number' => 'TN345678',
                'vehicle_type' => 'Van',
                'vehicle_plate' => '345 TU 6789',
                'is_active' => true,
                'is_available' => false,
            ],
        ];

        foreach ($drivers as $driverData) {
            Driver::create($driverData);
        }
    }
}
