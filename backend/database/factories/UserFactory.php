<?php

namespace Database\Factories;

use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class UserFactory extends Factory
{
    protected $model = User::class;

    public function definition(): array
    {
        return [
            'phone' => '+216' . $this->faker->numberBetween(10000000, 99999999),
            'email' => $this->faker->unique()->safeEmail(),
            'password' => Hash::make('Password123'),
            'first_name' => $this->faker->firstName(),
            'last_name' => $this->faker->lastName(),
            'store_name' => $this->faker->company() . ' Store',
            'store_type' => $this->faker->randomElement(['épicerie', 'superette', 'supérette', 'alimentation générale']),
            'address' => $this->faker->streetAddress(),
            'city' => $this->faker->randomElement(['Tunis', 'Sfax', 'Sousse', 'Kairouan', 'Bizerte', 'Gabès', 'Ariana', 'Gafsa', 'Monastir', 'Ben Arous']),
            'region' => $this->faker->randomElement(['Tunis', 'Sfax', 'Sousse', 'Nord', 'Centre', 'Sud']),
            'postal_code' => $this->faker->numberBetween(1000, 9999),
            'latitude' => $this->faker->latitude(32, 38),
            'longitude' => $this->faker->longitude(7, 12),
            'is_verified' => $this->faker->boolean(80),
            'is_active' => $this->faker->boolean(90),
            'email_verified_at' => now(),
            'phone_verified_at' => now(),
            'credit_score' => $this->faker->numberBetween(50, 95),
            'credit_limit' => $this->faker->randomElement([500, 1500, 5000, 15000]),
            'credit_used' => $this->faker->randomFloat(2, 0, 500),
            'credit_level' => $this->faker->randomElement(['bronze', 'silver', 'gold', 'platinum']),
            'remember_token' => Str::random(10),
        ];
    }

    public function unverified(): static
    {
        return $this->state(fn (array $attributes) => [
            'email_verified_at' => null,
            'phone_verified_at' => null,
            'is_verified' => false,
        ]);
    }

    public function inactive(): static
    {
        return $this->state(fn (array $attributes) => [
            'is_active' => false,
        ]);
    }
}
