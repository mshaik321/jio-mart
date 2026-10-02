<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

class CouponFactory extends Factory
{
    public function definition(): array
    {
        return [
            'code' => strtoupper(fake()->unique()->bothify('SAVE??##')),
            'type' => 'percentage',
            'value' => fake()->numberBetween(5, 30),
            'min_order_amount' => fake()->numberBetween(100, 500),
            'max_discount_amount' => fake()->numberBetween(50, 300),
            'starts_at' => now()->subDay(),
            'ends_at' => now()->addMonth(),
            'usage_limit' => 100,
            'per_customer_limit' => 1,
            'is_active' => true,
        ];
    }
}
