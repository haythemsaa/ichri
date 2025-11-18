<?php

namespace Database\Factories;

use App\Models\Product;
use App\Models\Category;
use App\Models\Brand;
use Illuminate\Database\Eloquent\Factories\Factory;

class ProductFactory extends Factory
{
    protected $model = Product::class;

    public function definition(): array
    {
        $price = $this->faker->randomFloat(3, 1, 100);
        $hasPromo = $this->faker->boolean(30);

        return [
            'category_id' => Category::factory(),
            'brand_id' => Brand::factory(),
            'name' => $this->faker->words(3, true),
            'name_ar' => 'منتج ' . $this->faker->word(),
            'slug' => $this->faker->unique()->slug(),
            'sku' => 'SKU-' . strtoupper($this->faker->bothify('???###')),
            'barcode' => $this->faker->ean13(),
            'description' => $this->faker->paragraph(),
            'description_ar' => $this->faker->paragraph(),
            'unit_type' => $this->faker->randomElement(['piece', 'pack', 'box', 'carton', 'bottle', 'kg', 'liter']),
            'units_per_pack' => $this->faker->randomElement([1, 6, 12, 24]),
            'packs_per_carton' => $this->faker->randomElement([6, 12, 24, 48]),
            'price_unit' => $price,
            'price_pack' => $price * $this->faker->randomElement([6, 12]),
            'price_carton' => $price * $this->faker->randomElement([24, 48, 72]),
            'promo_price' => $hasPromo ? $price * 0.85 : null,
            'promo_start' => $hasPromo ? now()->subDays(1) : null,
            'promo_end' => $hasPromo ? now()->addDays(7) : null,
            'stock_quantity' => $this->faker->numberBetween(0, 1000),
            'stock_alert_threshold' => 20,
            'is_active' => true,
            'is_featured' => $this->faker->boolean(20),
            'weight' => $this->faker->randomFloat(2, 0.1, 10),
            'dimensions' => json_encode([
                'length' => $this->faker->numberBetween(5, 50),
                'width' => $this->faker->numberBetween(5, 50),
                'height' => $this->faker->numberBetween(5, 50),
            ]),
            'image_url' => 'https://via.placeholder.com/300',
            'images' => json_encode([
                'https://via.placeholder.com/300',
                'https://via.placeholder.com/300/0000FF',
            ]),
            'tax_rate' => 19.00,
            'min_order_quantity' => 1,
            'max_order_quantity' => 100,
        ];
    }

    public function featured(): static
    {
        return $this->state(fn (array $attributes) => [
            'is_featured' => true,
        ]);
    }

    public function onSale(): static
    {
        return $this->state(function (array $attributes) {
            $price = $attributes['price_unit'];
            return [
                'promo_price' => $price * 0.80,
                'promo_start' => now()->subDays(1),
                'promo_end' => now()->addDays(14),
            ];
        });
    }

    public function outOfStock(): static
    {
        return $this->state(fn (array $attributes) => [
            'stock_quantity' => 0,
        ]);
    }

    public function inactive(): static
    {
        return $this->state(fn (array $attributes) => [
            'is_active' => false,
        ]);
    }
}
