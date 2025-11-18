<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Product;
use App\Models\Category;
use App\Models\Brand;
use App\Models\ProductImage;
use Illuminate\Support\Str;

class ProductSeeder extends Seeder
{
    public function run(): void
    {
        $laitCategory = Category::where('name', 'Lait')->first();
        $vitalaitBrand = Brand::where('name', 'Vitalait')->first();
        $deliceBrand = Brand::where('name', 'Délice')->first();

        $products = [
            [
                'category_id' => $laitCategory->id,
                'brand_id' => $vitalaitBrand->id,
                'name' => 'Lait Vitalait Entier 1L',
                'short_description' => 'Lait entier UHT',
                'description' => 'Lait entier UHT Vitalait de haute qualité, riche en vitamines et minéraux',
                'unit_price' => 1.450,
                'pack_price' => 8.400,
                'carton_price' => 33.600,
                'pack_quantity' => 6,
                'carton_quantity' => 24,
                'stock_quantity' => 500,
                'unit' => 'litre',
                'is_featured' => true,
                'is_bestseller' => true,
            ],
            [
                'category_id' => $laitCategory->id,
                'brand_id' => $deliceBrand->id,
                'name' => 'Lait Délice Demi-écrémé 1L',
                'short_description' => 'Lait demi-écrémé UHT',
                'description' => 'Lait demi-écrémé UHT Délice, idéal pour toute la famille',
                'unit_price' => 1.350,
                'pack_price' => 7.800,
                'carton_price' => 31.200,
                'pack_quantity' => 6,
                'carton_quantity' => 24,
                'stock_quantity' => 450,
                'unit' => 'litre',
                'is_featured' => true,
            ],
        ];

        // Ajouter plus de produits automatiquement
        $categories = Category::whereNotNull('parent_id')->get();
        $brands = Brand::all();

        foreach ($products as $productData) {
            $productData['sku'] = 'SKU-' . strtoupper(Str::random(8));
            $productData['barcode'] = '6' . str_pad(rand(100000000000, 999999999999), 12, '0', STR_PAD_LEFT);
            $productData['slug'] = Str::slug($productData['name']);
            $productData['is_active'] = true;
            $productData['low_stock_threshold'] = 50;

            $product = Product::create($productData);

            // Ajouter une image par défaut
            ProductImage::create([
                'product_id' => $product->id,
                'image_url' => 'https://via.placeholder.com/600x600?text=' . urlencode($product->name),
                'thumbnail_url' => 'https://via.placeholder.com/150x150?text=' . urlencode($product->name),
                'is_primary' => true,
                'order' => 0,
            ]);
        }

        // Générer 100 produits supplémentaires aléatoires
        for ($i = 0; $i < 100; $i++) {
            $category = $categories->random();
            $brand = $brands->random();

            $name = $brand->name . ' ' . $category->name . ' ' . Str::random(5);
            $price = rand(50, 5000) / 100; // Prix entre 0.50 et 50 TND

            $product = Product::create([
                'category_id' => $category->id,
                'brand_id' => $brand->id,
                'name' => $name,
                'slug' => Str::slug($name . '-' . $i),
                'sku' => 'SKU-' . strtoupper(Str::random(8)),
                'barcode' => '6' . str_pad(rand(100000000000, 999999999999), 12, '0', STR_PAD_LEFT),
                'short_description' => 'Produit de qualité',
                'description' => 'Description détaillée du produit',
                'unit_price' => $price,
                'pack_price' => $price * 6,
                'carton_price' => $price * 24,
                'pack_quantity' => 6,
                'carton_quantity' => 24,
                'stock_quantity' => rand(0, 1000),
                'low_stock_threshold' => 50,
                'is_active' => true,
                'is_featured' => rand(0, 10) > 8,
                'is_new' => rand(0, 10) > 7,
                'is_bestseller' => rand(0, 10) > 8,
                'is_on_sale' => rand(0, 10) > 7,
                'sale_price' => rand(0, 10) > 7 ? $price * 0.85 : null,
            ]);

            ProductImage::create([
                'product_id' => $product->id,
                'image_url' => 'https://via.placeholder.com/600x600?text=' . urlencode($product->name),
                'thumbnail_url' => 'https://via.placeholder.com/150x150?text=' . urlencode($product->name),
                'is_primary' => true,
                'order' => 0,
            ]);
        }
    }
}
