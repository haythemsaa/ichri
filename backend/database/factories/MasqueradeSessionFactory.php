<?php

namespace Database\Factories;

use App\Models\MasqueradeSession;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

class MasqueradeSessionFactory extends Factory
{
    protected $model = MasqueradeSession::class;

    public function definition(): array
    {
        $startedAt = $this->faker->dateTimeBetween('-1 week', 'now');

        return [
            'admin_user_id' => User::factory(),
            'target_user_id' => User::factory(),
            'reason' => $this->faker->sentence(),
            'started_at' => $startedAt,
            'ended_at' => $this->faker->optional(0.7)->dateTimeBetween($startedAt, 'now'),
            'ip_address' => $this->faker->ipv4(),
            'user_agent' => $this->faker->userAgent(),
            'actions_log' => $this->faker->optional(0.5)->randomElements([
                ['action' => 'order_created', 'details' => ['order_id' => rand(1, 100)], 'timestamp' => now()],
                ['action' => 'product_viewed', 'details' => ['product_id' => rand(1, 50)], 'timestamp' => now()],
            ]),
        ];
    }

    public function active(): static
    {
        return $this->state(fn (array $attributes) => [
            'ended_at' => null,
        ]);
    }

    public function ended(): static
    {
        return $this->state(fn (array $attributes) => [
            'ended_at' => now(),
        ]);
    }
}
