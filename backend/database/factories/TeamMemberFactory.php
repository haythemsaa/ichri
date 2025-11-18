<?php

namespace Database\Factories;

use App\Models\TeamMember;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

class TeamMemberFactory extends Factory
{
    protected $model = TeamMember::class;

    public function definition(): array
    {
        $role = $this->faker->randomElement(['admin', 'manager', 'employee', 'viewer']);

        $permissions = match($role) {
            'admin' => ['can_order', 'can_view_reports', 'can_manage_team', 'can_manage_inventory'],
            'manager' => ['can_order', 'can_view_reports', 'can_manage_inventory'],
            'employee' => ['can_order', 'can_view_products'],
            'viewer' => ['can_view_products', 'can_view_reports'],
        };

        return [
            'account_id' => User::factory(),
            'user_id' => User::factory(),
            'role' => $role,
            'permissions' => $permissions,
            'spending_limit' => $this->faker->optional(0.6)->randomElement([500, 1000, 2000, 5000]),
            'is_active' => $this->faker->boolean(90),
        ];
    }

    public function owner(): static
    {
        return $this->state(fn (array $attributes) => [
            'role' => 'owner',
            'permissions' => null,
            'spending_limit' => null,
            'is_active' => true,
        ]);
    }

    public function admin(): static
    {
        return $this->state(fn (array $attributes) => [
            'role' => 'admin',
            'permissions' => ['can_order', 'can_view_reports', 'can_manage_team', 'can_manage_inventory'],
            'spending_limit' => null,
        ]);
    }

    public function employee(): static
    {
        return $this->state(fn (array $attributes) => [
            'role' => 'employee',
            'permissions' => ['can_order', 'can_view_products'],
            'spending_limit' => 1000,
        ]);
    }

    public function inactive(): static
    {
        return $this->state(fn (array $attributes) => [
            'is_active' => false,
        ]);
    }
}
