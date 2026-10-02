<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

class DeliverySlotFactory extends Factory
{
    public function definition(): array
    {
        return [
            'name' => '9 AM - 12 PM',
            'start_time' => '09:00:00',
            'end_time' => '12:00:00',
            'max_orders' => 100,
            'is_active' => true,
        ];
    }
}
