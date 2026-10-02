<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

class UnitFactory extends Factory
{
    public function definition(): array
    {
        return [
            'name' => fake()->randomElement(['Kilogram', 'Gram', 'Litre', 'Millilitre', 'Piece', 'Packet']),
            'short_code' => fake()->randomElement(['kg', 'g', 'l', 'ml', 'pc', 'pkt']),
        ];
    }
}
