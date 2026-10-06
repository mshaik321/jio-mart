<?php

namespace Database\Seeders;

use App\Enums\InventoryMovementType;
use App\Enums\ProductStatus;
use App\Models\Category;
use App\Models\Product;
use App\Models\ProductVariant;
use App\Models\Unit;
use App\Models\Warehouse;
use App\Services\InventoryService;
use Illuminate\Database\Seeder;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

/**
 * Seeds the bilingual (English + Telugu) starter grocery catalog defined in
 * database/seeders/data/grocery-catalog.php.
 *
 * Safe to run repeatedly and against a live database: records are matched on
 * their stable slug/SKU and only ever created, never updated or deleted, so
 * admin edits (prices, names, images) and admin deletions are left alone.
 * Opening stock is written through InventoryService (so the ledger stays
 * consistent) and only for variants that have no stock record yet; it is
 * skipped in production unless CATALOG_SEED_OPENING_STOCK=true.
 */
class GroceryCatalogSeeder extends Seeder
{
    public const IMAGE_SOURCE_DIR = 'seeders/images/catalog';

    public const IMAGE_DISK_DIR = 'catalog';

    private const UNITS = [
        'g' => 'Gram', 'kg' => 'Kilogram', 'ml' => 'Millilitre', 'l' => 'Litre', 'pc' => 'Piece', 'pkt' => 'Packet',
    ];

    public function __construct(private InventoryService $inventory) {}

    public function run(): void
    {
        $catalog = require database_path('seeders/data/grocery-catalog.php');

        $units = collect(self::UNITS)->map(fn (string $name, string $code) => Unit::query()->firstOrCreate(['short_code' => $code], ['name' => $name]));

        $warehouse = $this->openingStockWarehouse();

        $categories = $this->seedCategories($catalog['categories']);

        foreach ($catalog['products'] as $position => $data) {
            $category = $categories[$data['category']] ?? null;

            if (! $category) {
                continue;
            }

            $this->seedProduct($data, $position + 1, $category, $catalog['categories'][$data['category']], $units, $warehouse);
        }

        $this->command?->info(sprintf('Grocery catalog ready: %d categories, %d products.', count($categories), count($catalog['products'])));
    }

    /**
     * @param  array<string, array<string, mixed>>  $definitions
     * @return array<string, Category>
     */
    private function seedCategories(array $definitions): array
    {
        $categories = [];
        $sortOrder = 0;

        foreach ($definitions as $slug => $data) {
            $sortOrder++;

            $category = Category::withTrashed()->firstOrCreate(['slug' => $slug], [
                'name' => $data['name'],
                'description' => $data['description'],
                'is_active' => true,
                'sort_order' => $sortOrder,
            ]);

            if ($category->trashed()) {
                continue; // Deleted by an admin; don't resurrect it or its products.
            }

            $category->translations()->firstOrCreate(['locale' => 'te'], [
                'name' => $data['te'],
                'description' => $data['te_description'],
            ]);

            if (! $category->image && ($path = $this->publishImage($data['image'], 'categories/'.$slug))) {
                $category->update(['image' => $path]);
            }

            $categories[$slug] = $category;
        }

        return $categories;
    }

    /**
     * @param  array<string, mixed>  $data
     * @param  array<string, mixed>  $categoryData
     * @param  Collection<string, Unit>  $units
     */
    private function seedProduct(array $data, int $sortOrder, Category $category, array $categoryData, Collection $units, ?Warehouse $warehouse): void
    {
        [$label, $unitCode, $weight, $mrp, $price] = $data['variants'][0];
        $flags = $data['flags'] ?? [];
        $note = $data['note'] ?? $categoryData['note'];
        $teNote = $data['te_note'] ?? $categoryData['te_note'];

        $product = Product::withTrashed()->where('sku', $data['sku'])->first();

        if ($product?->trashed()) {
            return;
        }

        $product ??= Product::create([
            'category_id' => $category->id,
            'unit_id' => $units[$unitCode]->id,
            'sku' => $data['sku'],
            'name' => $data['name'],
            'slug' => $this->availableSlug($data['slug'], $data['sku']),
            'short_description' => $data['summary'],
            'description' => $data['summary'].' '.$note,
            'weight' => $weight,
            'mrp' => $mrp,
            'selling_price' => $price,
            'tax_rate' => $data['tax'] ?? $categoryData['tax'],
            'min_order_qty' => 1,
            'max_order_qty' => 20,
            'status' => ProductStatus::Active,
            'is_featured' => in_array('featured', $flags, true),
            'is_bestseller' => in_array('bestseller', $flags, true),
            'is_new_arrival' => in_array('new', $flags, true),
            'sort_order' => $sortOrder,
        ]);

        $product->translations()->firstOrCreate(['locale' => 'te'], [
            'name' => $data['te'],
            'short_description' => $data['te_summary'],
            'description' => $data['te_summary'].' '.$teNote,
        ]);

        if ($product->images()->doesntExist() && ($path = $this->publishImage($data['slug'], 'products/'.$data['slug']))) {
            $product->images()->create(['path' => $path, 'alt_text' => $data['name'], 'is_primary' => true, 'sort_order' => 0]);
        }

        foreach ($data['variants'] as $index => [$variantLabel, , , $variantMrp, $variantPrice]) {
            $variant = ProductVariant::withTrashed()->firstOrCreate(
                ['sku' => $data['sku'].'-'.Str::upper(Str::slug($variantLabel, ''))],
                [
                    'product_id' => $product->id,
                    'variant_name' => $variantLabel,
                    'mrp' => $variantMrp,
                    'selling_price' => $variantPrice,
                    'is_default' => $index === 0,
                    'is_active' => true,
                ],
            );

            if ($warehouse && ! $variant->trashed()) {
                $this->seedOpeningStock($warehouse, $variant, $this->openingQuantity($data, $index));
            }
        }
    }

    private function seedOpeningStock(Warehouse $warehouse, ProductVariant $variant, int $quantity): void
    {
        if ($quantity <= 0 || $variant->inventories()->where('warehouse_id', $warehouse->id)->exists()) {
            return;
        }

        $this->inventory->recordMovement($warehouse, $variant, InventoryMovementType::Adjustment, $quantity, null, 'Opening stock (starter catalog seed)');
    }

    /**
     * Deterministic demo quantities (so re-seeding a fresh database gives the
     * same stock), with explicit overrides for the deliberately low / sold-out items.
     *
     * @param  array<string, mixed>  $data
     */
    private function openingQuantity(array $data, int $variantIndex): int
    {
        if (array_key_exists('stock', $data)) {
            return $variantIndex === 0 ? (int) $data['stock'] : 0;
        }

        return intdiv(40 + crc32($data['sku']) % 160, $variantIndex + 1);
    }

    private function openingStockWarehouse(): ?Warehouse
    {
        if (app()->isProduction() && ! config('catalog.seed_opening_stock')) {
            $this->command?->warn('Production: skipping opening stock (set CATALOG_SEED_OPENING_STOCK=true to seed it).');

            return null;
        }

        $warehouse = Warehouse::query()->where('is_active', true)->first();

        if (! $warehouse) {
            $this->command?->warn('No active warehouse: products were seeded without opening stock. Run OperationsSeeder first.');
        }

        return $warehouse;
    }

    /**
     * Copy a bundled catalog image onto the public disk (once) and return its disk path.
     */
    private function publishImage(string $imageKey, string $target): ?string
    {
        $source = database_path(self::IMAGE_SOURCE_DIR.'/'.$imageKey.'.webp');
        $path = self::IMAGE_DISK_DIR.'/'.$target.'.webp';

        if (! is_file($source)) {
            $this->command?->warn("Missing catalog image: {$imageKey}.webp");

            return null;
        }

        $disk = Storage::disk('public');

        if (! $disk->exists($path)) {
            $disk->put($path, file_get_contents($source));
        }

        return $path;
    }

    private function availableSlug(string $slug, string $sku): string
    {
        return Product::withTrashed()->where('slug', $slug)->exists() ? $slug.'-'.Str::lower($sku) : $slug;
    }
}
