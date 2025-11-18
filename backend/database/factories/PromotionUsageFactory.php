<?php

namespace Database\Factories;

use App\Models\PromotionUsage;
use App\Models\Promotion;
use App\Models\User;
use App\Models\Order;
use Illuminate\Database\Eloquent\Factories\Factory;

class PromotionUsageFactory extends Factory
{
    protected $model = PromotionUsage::class;

    public function definition(): array
    {
        return [
            'promotion_id' => Promotion::factory(),
            'user_id' => User::factory(),
            'order_id' => Order::factory(),
            'discount_amount' => $this->faker->randomFloat(3, 5, 100),
        ];
    }
}
