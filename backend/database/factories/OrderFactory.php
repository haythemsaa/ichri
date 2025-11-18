<?php

namespace Database\Factories;

use App\Models\Order;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

class OrderFactory extends Factory
{
    protected $model = Order::class;

    public function definition(): array
    {
        $subtotal = $this->faker->randomFloat(3, 50, 1000);
        $tax = $subtotal * 0.19;
        $discount = $this->faker->randomFloat(3, 0, $subtotal * 0.2);
        $deliveryFee = $this->faker->randomElement([0, 5, 10, 15]);
        $total = $subtotal + $tax - $discount + $deliveryFee;

        return [
            'user_id' => User::factory(),
            'order_number' => 'ORD-' . strtoupper($this->faker->bothify('????####')),
            'status' => $this->faker->randomElement(['pending', 'confirmed', 'preparing', 'ready', 'shipped', 'delivered']),
            'payment_status' => $this->faker->randomElement(['pending', 'paid', 'failed']),
            'payment_method' => $this->faker->randomElement(['cash', 'credit', 'card', 'mobile_money']),
            'subtotal_amount' => $subtotal,
            'tax_amount' => $tax,
            'discount_amount' => $discount,
            'delivery_fee' => $deliveryFee,
            'total_amount' => $total,
            'delivery_address' => $this->faker->address(),
            'delivery_latitude' => $this->faker->latitude(32, 38),
            'delivery_longitude' => $this->faker->longitude(7, 12),
            'delivery_notes' => $this->faker->boolean(50) ? $this->faker->sentence() : null,
            'delivery_date' => $this->faker->dateTimeBetween('now', '+7 days'),
            'notes' => $this->faker->boolean(30) ? $this->faker->sentence() : null,
        ];
    }

    public function pending(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'pending',
            'payment_status' => 'pending',
        ]);
    }

    public function confirmed(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'confirmed',
            'payment_status' => 'paid',
        ]);
    }

    public function delivered(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'delivered',
            'payment_status' => 'paid',
        ]);
    }

    public function cancelled(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'cancelled',
        ]);
    }
}
