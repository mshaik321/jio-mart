<?php

namespace Database\Factories;

use App\Models\Store;
use Illuminate\Database\Eloquent\Factories\Factory;

class WarehouseFactory extends Factory
{
    public function definition(): array
    {
        return [
            'store_id' => Store::factory(),
            'name' => fake()->city().' Warehouse',
            'code' => 'WH-'.fake()->unique()->numberBetween(1000, 9999),
            'address' => fake()->address(),
            'is_active' => true,
        ];
    }
}
