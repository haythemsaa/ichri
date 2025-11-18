<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Category;
use Illuminate\Support\Str;

class CategorySeeder extends Seeder
{
    public function run(): void
    {
        $categories = [
            [
                'name' => 'Produits Laitiers',
                'icon' => '🥛',
                'order' => 1,
                'children' => [
                    'Lait',
                    'Yaourts',
                    'Fromages',
                    'Beurre & Crème',
                ]
            ],
            [
                'name' => 'Boissons',
                'icon' => '🥤',
                'order' => 2,
                'children' => [
                    'Eau',
                    'Sodas',
                    'Jus',
                    'Boissons chaudes',
                ]
            ],
            [
                'name' => 'Épicerie Salée',
                'icon' => '🍝',
                'order' => 3,
                'children' => [
                    'Pâtes & Riz',
                    'Conserves',
                    'Huiles & Sauces',
                    'Condiments',
                ]
            ],
            [
                'name' => 'Épicerie Sucrée',
                'icon' => '🍪',
                'order' => 4,
                'children' => [
                    'Biscuits',
                    'Chocolat',
                    'Confiseries',
                    'Céréales',
                ]
            ],
            [
                'name' => 'Hygiène & Beauté',
                'icon' => '🧴',
                'order' => 5,
                'children' => [
                    'Soins corporels',
                    'Soins cheveux',
                    'Hygiène bucale',
                    'Cosmétiques',
                ]
            ],
            [
                'name' => 'Entretien',
                'icon' => '🧹',
                'order' => 6,
                'children' => [
                    'Lessive',
                    'Vaisselle',
                    'Nettoyants',
                    'Désinfectants',
                ]
            ],
        ];

        foreach ($categories as $categoryData) {
            $children = $categoryData['children'] ?? [];
            unset($categoryData['children']);

            $categoryData['slug'] = Str::slug($categoryData['name']);
            $categoryData['is_active'] = true;

            $category = Category::create($categoryData);

            foreach ($children as $childName) {
                Category::create([
                    'parent_id' => $category->id,
                    'name' => $childName,
                    'slug' => Str::slug($childName),
                    'is_active' => true,
                    'order' => 0,
                ]);
            }
        }
    }
}
