<?php

namespace Database\Factories;

use App\Models\Product;
use Illuminate\Database\Eloquent\Factories\Factory;

class ProductVariantFactory extends Factory
{
    public function definition(): array
    {
        $mrp = fake()->randomFloat(2, 20, 2000);

        return [
            'product_id' => Product::factory(),
            'sku' => 'VAR-'.strtoupper(fake()->unique()->bothify('????####')),
            'variant_name' => fake()->randomElement(['250 G', '500 G', '1 KG', '2 KG', '5 KG']),
            'attributes' => ['pack' => fake()->randomElement(['pouch', 'box', 'jar'])],
            'mrp' => $mrp,
            'selling_price' => round($mrp * fake()->randomFloat(2, 0.75, 0.98), 2),
            'is_default' => false,
            'is_active' => true,
        ];
    }

    public function default(): static
    {
        return $this->state(['is_default' => true]);
    }
}
