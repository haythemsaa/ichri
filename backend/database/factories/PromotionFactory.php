<?php

namespace Database\Factories;

use App\Models\Promotion;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

class PromotionFactory extends Factory
{
    protected $model = Promotion::class;

    public function definition(): array
    {
        $type = $this->faker->randomElement(['percentage', 'fixed_amount', 'bogo', 'bundle']);

        return [
            'name' => $this->faker->sentence(3),
            'code' => $this->faker->boolean(50) ? strtoupper(Str::random(8)) : null,
            'description' => $this->faker->sentence(10),
            'type' => $type,
            'config' => $type === 'bundle' ? ['buy' => 2, 'get' => 1] : null,
            'discount_value' => $type === 'percentage'
                ? $this->faker->numberBetween(5, 50)
                : $this->faker->randomFloat(3, 5, 100),
            'min_purchase' => $this->faker->boolean(50) ? $this->faker->randomFloat(3, 20, 100) : null,
            'usage_limit_per_user' => $this->faker->boolean(30) ? $this->faker->numberBetween(1, 5) : null,
            'usage_limit_total' => $this->faker->boolean(30) ? $this->faker->numberBetween(10, 1000) : null,
            'usage_count' => 0,
            'start_date' => now()->subDays($this->faker->numberBetween(1, 7)),
            'end_date' => now()->addDays($this->faker->numberBetween(7, 30)),
            'is_active' => true,
            'is_featured' => $this->faker->boolean(30),
            'terms' => $this->faker->paragraph(),
        ];
    }

    public function percentage(): static
    {
        return $this->state(fn (array $attributes) => [
            'type' => 'percentage',
            'discount_value' => $this->faker->numberBetween(10, 50),
        ]);
    }

    public function fixedAmount(): static
    {
        return $this->state(fn (array $attributes) => [
            'type' => 'fixed_amount',
            'discount_value' => $this->faker->randomFloat(3, 10, 50),
        ]);
    }

    public function bogo(): static
    {
        return $this->state(fn (array $attributes) => [
            'type' => 'bogo',
            'discount_value' => 0,
        ]);
    }

    public function bundle(): static
    {
        return $this->state(fn (array $attributes) => [
            'type' => 'bundle',
            'config' => ['buy' => 2, 'get' => 1],
            'discount_value' => 0,
        ]);
    }

    public function featured(): static
    {
        return $this->state(fn (array $attributes) => [
            'is_featured' => true,
        ]);
    }

    public function inactive(): static
    {
        return $this->state(fn (array $attributes) => [
            'is_active' => false,
        ]);
    }

    public function expired(): static
    {
        return $this->state(fn (array $attributes) => [
            'start_date' => now()->subDays(30),
            'end_date' => now()->subDays(1),
        ]);
    }

    public function withCode(string $code): static
    {
        return $this->state(fn (array $attributes) => [
            'code' => $code,
        ]);
    }

    public function withMinPurchase(float $amount): static
    {
        return $this->state(fn (array $attributes) => [
            'min_purchase' => $amount,
        ]);
    }
}
