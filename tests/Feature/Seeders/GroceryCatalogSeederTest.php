<?php

namespace Tests\Feature\Seeders;

use App\Enums\InventoryMovementType;
use App\Models\Category;
use App\Models\Inventory;
use App\Models\InventoryMovement;
use App\Models\Product;
use App\Models\ProductVariant;
use Database\Seeders\GroceryCatalogSeeder;
use Database\Seeders\OperationsSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class GroceryCatalogSeederTest extends TestCase
{
    use RefreshDatabase;

    private function seedCatalog(): void
    {
        $this->seed([OperationsSeeder::class, GroceryCatalogSeeder::class]);
    }

    /**
     * @return array<string, mixed>
     */
    private function catalogData(): array
    {
        return require database_path('seeders/data/grocery-catalog.php');
    }

    public function test_every_catalog_product_ships_with_its_own_optimised_image(): void
    {
        foreach ($this->catalogData()['products'] as $product) {
            $path = database_path(GroceryCatalogSeeder::IMAGE_SOURCE_DIR.'/'.$product['slug'].'.webp');

            $this->assertFileExists($path);
            $this->assertLessThan(60 * 1024, filesize($path), "{$product['slug']}.webp should stay small");
            $this->assertSame([600, 600], array_slice(getimagesize($path), 0, 2));
        }
    }

    public function test_seeds_the_bilingual_catalog_with_images_and_categories(): void
    {
        Storage::fake('public');

        $this->seedCatalog();

        $this->assertSame(15, Category::count());
        $this->assertSame(count($this->catalogData()['products']), Product::count());
        $this->assertGreaterThanOrEqual(120, Product::count());

        Product::with(['translations', 'primaryImage', 'variants'])->each(function (Product $product) {
            $this->assertNotEmpty($product->name);
            $this->assertNotEmpty($product->translationFor('name', 'te'), "{$product->sku} has no Telugu name");
            $this->assertNotSame($product->name, $product->translationFor('name', 'te'));
            $this->assertNotEmpty($product->translationFor('description', 'te'));
            $this->assertNotNull($product->primaryImage, "{$product->sku} has no image");
            Storage::disk('public')->assertExists($product->primaryImage->path);
            $this->assertSame(1, $product->variants->where('is_default', true)->count());
            $this->assertStringStartsWith('GRO-', $product->sku);
        });

        Category::with('translations')->each(function (Category $category) {
            $this->assertNotEmpty($category->translationFor('name', 'te'));
            Storage::disk('public')->assertExists($category->image);
        });
    }

    public function test_required_staples_are_present_with_the_expected_telugu_names(): void
    {
        Storage::fake('public');

        $this->seedCatalog();

        $expected = [
            'GRO-GROUNDNUT-001' => 'వేరుశెనగలు', 'GRO-PEANUT-001' => 'వేయించిన పల్లీలు', 'GRO-ALMOND-001' => 'బాదం',
            'GRO-CASHEW-001' => 'జీడిపప్పు', 'GRO-PISTACHIO-001' => 'పిస్తా', 'GRO-WALNUT-001' => 'ఆక్రోట్లు',
            'GRO-RAISIN-001' => 'ఎండు ద్రాక్ష', 'GRO-DATES-001' => 'ఖర్జూరం', 'GRO-ANJEER-001' => 'అంజీర్',
            'GRO-PUMPKINSEED-001' => 'గుమ్మడి గింజలు', 'GRO-SUNFLOWERSEED-001' => 'పొద్దుతిరుగుడు గింజలు',
            'GRO-FLAXSEED-001' => 'అవిసె గింజలు', 'GRO-SESAME-001' => 'తెల్ల నువ్వులు', 'GRO-CHIA-001' => 'చియా గింజలు',
            'GRO-RICE-001' => 'బియ్యం', 'GRO-BASMATI-001' => 'బాస్మతి బియ్యం', 'GRO-SONAMASOORI-001' => 'సోనా మసూరి బియ్యం',
            'GRO-TOORDAL-001' => 'కందిపప్పు', 'GRO-MOONGDAL-001' => 'పెసరపప్పు', 'GRO-CHANADAL-001' => 'శనగపప్పు',
            'GRO-URADDAL-001' => 'మినపప్పు', 'GRO-ATTA-001' => 'గోధుమ పిండి', 'GRO-MAIDA-001' => 'మైదా పిండి',
            'GRO-RAVA-001' => 'బొంబాయి రవ్వ', 'GRO-SUGAR-001' => 'చక్కెర', 'GRO-SALT-001' => 'ఉప్పు',
            'GRO-TURMERIC-001' => 'పసుపు', 'GRO-CHILLIPOWDER-001' => 'కారం', 'GRO-CORIANDERPOWDER-001' => 'ధనియాల పొడి',
            'GRO-CUMIN-001' => 'జీలకర్ర', 'GRO-MUSTARD-001' => 'ఆవాలు', 'GRO-COOKINGOIL-001' => 'వంట నూనె (పామోలిన్)',
            'GRO-SUNFLOWEROIL-001' => 'సన్‌ఫ్లవర్ నూనె', 'GRO-GROUNDNUTOIL-001' => 'వేరుశెనగ నూనె', 'GRO-TEA-001' => 'టీ పొడి',
            'GRO-COFFEE-001' => 'ఇన్‌స్టంట్ కాఫీ', 'GRO-MARIE-001' => 'మేరీ బిస్కెట్లు', 'GRO-POHA-001' => 'అటుకులు',
            'GRO-OATS-001' => 'ఓట్స్',
        ];

        $products = Product::with('translations')->whereIn('sku', array_keys($expected))->get()->keyBy('sku');

        foreach ($expected as $sku => $telugu) {
            $this->assertTrue($products->has($sku), "Missing {$sku}");
            $this->assertSame($telugu, $products[$sku]->translationFor('name', 'te'), "Telugu name for {$sku}");
        }
    }

    public function test_reseeding_never_duplicates_overwrites_or_resurrects_records(): void
    {
        Storage::fake('public');
        $this->seedCatalog();

        $counts = fn () => [Category::count(), Product::withTrashed()->count(), ProductVariant::withTrashed()->count(), Inventory::count(), InventoryMovement::count()];
        $before = $counts();

        Product::where('sku', 'GRO-ALMOND-001')->first()->update(['name' => 'Premium Almonds', 'selling_price' => 299]);
        Product::where('sku', 'GRO-CASHEW-001')->first()->delete();

        $this->seedCatalog();

        $this->assertSame($before, $counts());
        $this->assertSame('Premium Almonds', Product::where('sku', 'GRO-ALMOND-001')->value('name'));
        $this->assertSame(299.0, (float) Product::where('sku', 'GRO-ALMOND-001')->value('selling_price'));
        $this->assertTrue(Product::withTrashed()->where('sku', 'GRO-CASHEW-001')->first()->trashed());
    }

    public function test_opening_stock_goes_through_the_inventory_ledger(): void
    {
        Storage::fake('public');

        $this->seedCatalog();

        $this->assertSame(Inventory::count(), InventoryMovement::where('type', InventoryMovementType::Adjustment)->count());
        Inventory::query()->each(fn (Inventory $inventory) => $this->assertSame(
            $inventory->quantity_on_hand,
            (int) InventoryMovement::where('product_variant_id', $inventory->product_variant_id)->sum('quantity'),
        ));

        $soldOut = Product::where('sku', 'GRO-CRANBERRY-001')->first()->variants->first();
        $this->assertSame(0, $soldOut->totalStock());
        $this->assertSame(6, Product::where('sku', 'GRO-PISTACHIO-001')->first()->variants->first()->totalStock());
    }

    public function test_production_seeding_skips_opening_stock_unless_explicitly_enabled(): void
    {
        Storage::fake('public');
        $this->app['env'] = 'production';
        $seedInProduction = fn (string $seeder) => $this->artisan('db:seed', ['--class' => $seeder, '--force' => true])->assertSuccessful();

        $seedInProduction(OperationsSeeder::class);
        $seedInProduction(GroceryCatalogSeeder::class);

        $this->assertSame(count($this->catalogData()['products']), Product::count());
        $this->assertSame(0, Inventory::count());

        config(['catalog.seed_opening_stock' => true]);
        $seedInProduction(GroceryCatalogSeeder::class);

        $this->assertGreaterThan(0, Inventory::count());
    }
}
