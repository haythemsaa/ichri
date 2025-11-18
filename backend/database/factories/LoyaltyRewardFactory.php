<?php

namespace Database\Factories;

use App\Models\LoyaltyReward;
use Illuminate\Database\Eloquent\Factories\Factory;

class LoyaltyRewardFactory extends Factory
{
    protected $model = LoyaltyReward::class;

    public function definition(): array
    {
        $type = $this->faker->randomElement(['discount', 'product', 'cashback', 'free_delivery']);

        $config = match($type) {
            'discount' => [
                'amount' => $this->faker->randomElement([5, 10, 25, 50]),
                'min_order_amount' => $this->faker->randomElement([50, 100, 200]),
            ],
            'cashback' => [
                'amount' => $this->faker->randomElement([10, 20, 50]),
            ],
            'product' => [
                'product_id' => null,
                'category' => $this->faker->randomElement(['cafe', 'huile', 'sucre']),
            ],
            'free_delivery' => [
                'min_order_amount' => 0,
            ],
        };

        return [
            'name' => $this->faker->sentence(3),
            'description' => $this->faker->sentence(10),
            'type' => $type,
            'points_cost' => $this->faker->randomElement([200, 500, 1000, 1500, 2000, 2500]),
            'config' => $config,
            'quantity_available' => $this->faker->optional(0.7)->numberBetween(10, 100),
            'is_active' => $this->faker->boolean(80),
            'valid_from' => $this->faker->optional(0.3)->dateTimeBetween('-1 month', 'now'),
            'valid_until' => $this->faker->optional(0.3)->dateTimeBetween('now', '+3 months'),
        ];
    }

    public function active(): static
    {
        return $this->state(fn (array $attributes) => [
            'is_active' => true,
            'valid_from' => null,
            'valid_until' => null,
        ]);
    }

    public function inactive(): static
    {
        return $this->state(fn (array $attributes) => [
            'is_active' => false,
        ]);
    }

    public function unlimited(): static
    {
        return $this->state(fn (array $attributes) => [
            'quantity_available' => null,
        ]);
    }

    public function limited(int $quantity): static
    {
        return $this->state(fn (array $attributes) => [
            'quantity_available' => $quantity,
        ]);
    }
}
