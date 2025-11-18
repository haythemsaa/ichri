<?php

namespace Database\Factories;

use App\Models\KarnyCustomer;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

class KarnyCustomerFactory extends Factory
{
    protected $model = KarnyCustomer::class;

    public function definition(): array
    {
        return [
            'user_id' => User::factory(),
            'name' => $this->faker->name(),
            'phone' => '+216' . $this->faker->numberBetween(10000000, 99999999),
            'qr_code' => 'KARNY-' . strtoupper(Str::random(12)),
            'credit_limit' => $this->faker->randomElement([200, 500, 1000, 2000]),
            'current_balance' => $this->faker->randomFloat(3, 0, 500),
        ];
    }

    public function withHighBalance(): static
    {
        return $this->state(fn (array $attributes) => [
            'current_balance' => $this->faker->randomFloat(3, 800, 1500),
            'credit_limit' => 2000,
        ]);
    }

    public function withNoBalance(): static
    {
        return $this->state(fn (array $attributes) => [
            'current_balance' => 0,
        ]);
    }
}
