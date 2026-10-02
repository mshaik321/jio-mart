<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

class StoreFactory extends Factory
{
    public function definition(): array
    {
        return [
            'name' => fake()->city().' Store',
            'code' => 'STR-'.fake()->unique()->numberBetween(1000, 9999),
            'phone' => fake()->numerify('9#########'),
            'address' => fake()->address(),
            'is_active' => true,
        ];
    }
}
