<?php

namespace Database\Factories;

use App\Models\DigitalServiceTransaction;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

class DigitalServiceTransactionFactory extends Factory
{
    protected $model = DigitalServiceTransaction::class;

    public function definition(): array
    {
        $serviceType = $this->faker->randomElement(['mobile_topup', 'electricity_bill', 'water_bill', 'internet_bill']);
        $amount = $this->faker->randomFloat(3, 5, 200);

        // Commission rates based on service type
        $commissionRate = match ($serviceType) {
            'mobile_topup' => 0.03, // 3%
            'electricity_bill' => 0.015, // 1.5%
            'water_bill' => 0.015, // 1.5%
            'internet_bill' => 0.02, // 2%
            default => 0.03,
        };

        $provider = match ($serviceType) {
            'mobile_topup' => $this->faker->randomElement(['ooredoo', 'orange', 'tunisie_telecom']),
            'electricity_bill' => 'steg',
            'water_bill' => 'sonede',
            'internet_bill' => $this->faker->randomElement(['topnet', 'globalnet', 'tunisie_telecom']),
            default => 'unknown',
        };

        return [
            'user_id' => User::factory(),
            'transaction_ref' => 'DS-' . date('Ymd') . '-' . strtoupper(Str::random(8)),
            'service_type' => $serviceType,
            'provider' => $provider,
            'recipient_number' => $this->faker->numerify('########'),
            'amount' => $amount,
            'commission' => round($amount * $commissionRate, 3),
            'status' => $this->faker->randomElement(['pending', 'completed', 'failed']),
        ];
    }

    public function mobileTopup(): static
    {
        return $this->state(function (array $attributes) {
            $amount = $this->faker->randomElement([5, 10, 20, 30]);
            return [
                'service_type' => 'mobile_topup',
                'provider' => $this->faker->randomElement(['ooredoo', 'orange', 'tunisie_telecom']),
                'amount' => $amount,
                'commission' => round($amount * 0.03, 3),
            ];
        });
    }

    public function electricityBill(): static
    {
        return $this->state(function (array $attributes) {
            $amount = $this->faker->randomFloat(3, 20, 500);
            return [
                'service_type' => 'electricity_bill',
                'provider' => 'steg',
                'amount' => $amount,
                'commission' => round($amount * 0.015, 3),
            ];
        });
    }

    public function waterBill(): static
    {
        return $this->state(function (array $attributes) {
            $amount = $this->faker->randomFloat(3, 10, 200);
            return [
                'service_type' => 'water_bill',
                'provider' => 'sonede',
                'amount' => $amount,
                'commission' => round($amount * 0.015, 3),
            ];
        });
    }

    public function completed(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'completed',
        ]);
    }

    public function failed(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'failed',
        ]);
    }
}
