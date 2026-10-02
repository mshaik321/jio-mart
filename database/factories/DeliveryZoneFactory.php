<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

class DeliveryZoneFactory extends Factory
{
    public function definition(): array
    {
        return [
            'name' => fake()->city().' Zone',
            'pincodes' => [fake()->numerify('######'), fake()->numerify('######')],
            'delivery_charge' => fake()->randomElement([0, 20, 30, 40]),
            'free_delivery_above' => 499,
            'is_active' => true,
        ];
    }
}
