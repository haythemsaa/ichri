<?php

namespace Database\Factories;

use App\Models\LoyaltyRedemption;
use App\Models\User;
use App\Models\LoyaltyReward;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

class LoyaltyRedemptionFactory extends Factory
{
    protected $model = LoyaltyRedemption::class;

    public function definition(): array
    {
        return [
            'user_id' => User::factory(),
            'reward_id' => LoyaltyReward::factory(),
            'points_spent' => $this->faker->numberBetween(100, 2000),
            'status' => $this->faker->randomElement(['pending', 'approved', 'used', 'cancelled', 'expired']),
            'redemption_code' => strtoupper(Str::random(12)),
            'expires_at' => now()->addDays(30),
            'used_at' => null,
        ];
    }

    public function pending(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'pending',
            'used_at' => null,
        ]);
    }

    public function approved(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'approved',
            'used_at' => null,
        ]);
    }

    public function used(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'used',
            'used_at' => now(),
        ]);
    }

    public function cancelled(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'cancelled',
        ]);
    }

    public function expired(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'expired',
            'expires_at' => now()->subDays(1),
        ]);
    }
}
