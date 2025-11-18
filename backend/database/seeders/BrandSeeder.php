<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Brand;
use Illuminate\Support\Str;

class BrandSeeder extends Seeder
{
    public function run(): void
    {
        $brands = [
            // Marques tunisiennes
            'Vitalait',
            'Délice',
            'Sicam',
            'Stil',
            'Trefle',
            'Mabrouk',
            'Zine',
            'El Mazraa',
            'Okay',
            'Tounsia',

            // Marques internationales
            'Coca-Cola',
            'Pepsi',
            'Nestlé',
            'Danone',
            'Panzani',
            'Barilla',
            'Nutella',
            'Ariel',
            'OMO',
            'Dove',
            'Nivea',
            'Colgate',
            'Gillette',
            'Pampers',
        ];

        foreach ($brands as $brandName) {
            Brand::create([
                'name' => $brandName,
                'slug' => Str::slug($brandName),
                'is_active' => true,
                'is_featured' => in_array($brandName, ['Vitalait', 'Délice', 'Coca-Cola', 'Nestlé']),
            ]);
        }
    }
}
