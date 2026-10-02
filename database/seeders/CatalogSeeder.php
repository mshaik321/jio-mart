<?php

namespace Database\Seeders;

use App\Enums\ProductStatus;
use App\Models\Brand;
use App\Models\Category;
use App\Models\Inventory;
use App\Models\Product;
use App\Models\ProductVariant;
use App\Models\Unit;
use App\Models\Warehouse;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class CatalogSeeder extends Seeder
{
    /**
     * @var array<string, list<string>>
     */
    private array $categories = [
        'Fruits & Vegetables' => ['Fresh Fruits', 'Fresh Vegetables', 'Herbs & Seasonings'],
        'Dairy & Breakfast' => ['Milk', 'Bread & Bakery', 'Eggs', 'Cheese & Butter'],
        'Staples' => ['Atta, Rice & Dal', 'Edible Oils & Ghee', 'Masalas & Spices'],
        'Snacks & Beverages' => ['Chips & Namkeen', 'Tea & Coffee', 'Cold Drinks & Juices'],
        'Personal Care' => ['Bath & Body', 'Hair Care', 'Oral Care'],
        'Household' => ['Cleaning Essentials', 'Detergents', 'Kitchen Tools'],
    ];

    private array $units = [
        ['name' => 'Kilogram', 'short_code' => 'kg'],
        ['name' => 'Gram', 'short_code' => 'g'],
        ['name' => 'Litre', 'short_code' => 'l'],
        ['name' => 'Millilitre', 'short_code' => 'ml'],
        ['name' => 'Piece', 'short_code' => 'pc'],
        ['name' => 'Packet', 'short_code' => 'pkt'],
    ];

    public function run(): void
    {
        $units = collect($this->units)->map(fn ($u) => Unit::query()->firstOrCreate(['short_code' => $u['short_code']], $u));

        $brands = Brand::factory()->count(10)->create();

        $warehouse = Warehouse::query()->first() ?? Warehouse::factory()->create();

        foreach ($this->categories as $parentName => $children) {
            $parent = Category::query()->firstOrCreate(
                ['slug' => Str::slug($parentName)],
                ['name' => $parentName, 'is_active' => true],
            );

            foreach ($children as $childName) {
                $child = Category::query()->firstOrCreate(
                    ['slug' => Str::slug($parentName.'-'.$childName)],
                    ['parent_id' => $parent->id, 'name' => $childName, 'is_active' => true],
                );

                Product::factory()
                    ->count(4)
                    ->create([
                        'category_id' => $child->id,
                        'brand_id' => fn () => $brands->random()->id,
                        'unit_id' => fn () => $units->random()->id,
                        'status' => ProductStatus::Active,
                    ])
                    ->each(function (Product $product) use ($warehouse) {
                        $variant = ProductVariant::factory()->default()->create([
                            'product_id' => $product->id,
                            'mrp' => $product->mrp,
                            'selling_price' => $product->selling_price,
                        ]);

                        Inventory::query()->create([
                            'warehouse_id' => $warehouse->id,
                            'product_variant_id' => $variant->id,
                            'quantity_on_hand' => fake()->numberBetween(20, 200),
                            'low_stock_threshold' => 10,
                        ]);
                    });
            }
        }
    }
}
