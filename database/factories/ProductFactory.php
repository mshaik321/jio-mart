<?php

namespace Database\Factories;

use App\Enums\ProductStatus;
use App\Models\Brand;
use App\Models\Category;
use App\Models\Unit;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

class ProductFactory extends Factory
{
    public function definition(): array
    {
        $name = fake()->unique()->words(3, true);
        $mrp = fake()->randomFloat(2, 20, 2000);

        return [
            'category_id' => Category::factory(),
            'brand_id' => Brand::factory(),
            'unit_id' => Unit::factory(),
            'sku' => 'SKU-'.strtoupper(fake()->unique()->bothify('????####')),
            'name' => Str::title($name),
            'slug' => Str::slug($name).'-'.fake()->unique()->numberBetween(1, 1000000),
            'description' => fake()->paragraph(),
            'short_description' => fake()->sentence(),
            'weight' => fake()->randomFloat(3, 0.1, 10),
            'mrp' => $mrp,
            'selling_price' => round($mrp * fake()->randomFloat(2, 0.75, 0.98), 2),
            'tax_rate' => fake()->randomElement([0, 5, 12, 18]),
            'min_order_qty' => 1,
            'max_order_qty' => 20,
            'status' => ProductStatus::Active,
            'is_featured' => fake()->boolean(20),
            'is_bestseller' => fake()->boolean(20),
            'is_new_arrival' => fake()->boolean(20),
        ];
    }
}
