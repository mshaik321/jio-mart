<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

class DeliveryAgentFactory extends Factory
{
    public function definition(): array
    {
        return [
            'name' => fake()->name(),
            'phone' => fake()->unique()->numerify('9#########'),
            'vehicle_type' => fake()->randomElement(['Bike', 'Scooter', 'Van']),
            'vehicle_number' => strtoupper(fake()->bothify('??##??####')),
            'status' => 'offline',
            'is_active' => true,
        ];
    }
}
