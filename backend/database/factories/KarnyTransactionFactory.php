<?php

namespace Database\Factories;

use App\Models\KarnyTransaction;
use App\Models\KarnyCustomer;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

class KarnyTransactionFactory extends Factory
{
    protected $model = KarnyTransaction::class;

    public function definition(): array
    {
        $type = $this->faker->randomElement(['credit', 'payment']);

        return [
            'karny_customer_id' => KarnyCustomer::factory(),
            'user_id' => User::factory(),
            'type' => $type,
            'amount' => $this->faker->randomFloat(3, 5, 200),
            'description' => $type === 'credit' ? $this->faker->sentence(3) : null,
            'due_date' => $type === 'credit' ? $this->faker->dateTimeBetween('now', '+30 days') : null,
            'status' => $type === 'credit'
                ? $this->faker->randomElement(['pending', 'paid', 'overdue'])
                : 'completed',
            'paid_at' => $type === 'payment' ? now() : null,
        ];
    }

    public function credit(): static
    {
        return $this->state(fn (array $attributes) => [
            'type' => 'credit',
            'status' => 'pending',
            'due_date' => $this->faker->dateTimeBetween('now', '+30 days'),
        ]);
    }

    public function payment(): static
    {
        return $this->state(fn (array $attributes) => [
            'type' => 'payment',
            'status' => 'completed',
            'due_date' => null,
            'paid_at' => now(),
        ]);
    }

    public function overdue(): static
    {
        return $this->state(fn (array $attributes) => [
            'type' => 'credit',
            'status' => 'overdue',
            'due_date' => $this->faker->dateTimeBetween('-30 days', '-1 day'),
        ]);
    }
}
