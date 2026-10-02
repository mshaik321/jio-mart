<?php

namespace Tests\Feature\Storefront;

use App\Enums\ProductStatus;
use App\Models\Category;
use App\Models\Inventory;
use App\Models\Product;
use App\Models\ProductVariant;
use App\Models\Warehouse;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class BrowsingTest extends TestCase
{
    use RefreshDatabase;

    private function makeStockedVariant(array $productAttrs = []): ProductVariant
    {
        $warehouse = Warehouse::factory()->create(['is_active' => true]);
        $product = Product::factory()->create(array_merge(['status' => ProductStatus::Active], $productAttrs));
        $variant = ProductVariant::factory()->default()->create(['product_id' => $product->id]);

        Inventory::create([
            'warehouse_id' => $warehouse->id,
            'product_variant_id' => $variant->id,
            'quantity_on_hand' => 20,
        ]);

        return $variant;
    }

    public function test_home_page_loads(): void
    {
        $this->makeStockedVariant(['is_featured' => true]);

        $this->get('/')->assertOk()->assertSee('GroceryHub');
    }

    public function test_category_page_lists_products(): void
    {
        $variant = $this->makeStockedVariant();
        $category = $variant->product->category;

        $this->get(route('categories.show', $category->slug))
            ->assertOk()
            ->assertSee($variant->product->name);
    }

    public function test_search_finds_product_by_name(): void
    {
        $variant = $this->makeStockedVariant(['name' => 'Organic Basmati Rice', 'slug' => 'organic-basmati-rice']);

        $this->get(route('search', ['q' => 'Basmati']))
            ->assertOk()
            ->assertSee('Organic Basmati Rice');
    }

    public function test_product_detail_page_shows_variant_and_price(): void
    {
        $variant = $this->makeStockedVariant();

        $this->get(route('products.show', $variant->product->slug))
            ->assertOk()
            ->assertSee($variant->product->name)
            ->assertSee(number_format((float) $variant->selling_price, 2));
    }

    public function test_category_page_404s_for_unknown_slug(): void
    {
        $this->get('/categories/does-not-exist')->assertNotFound();
    }

    /**
     * Livewire full-page components need an explicit #[Layout(...)] attribute or Livewire
     * throws "page component layout view not found" — Livewire::test() doesn't exercise that
     * path, only a real route hit does, so these three must be real HTTP requests.
     */
    public function test_cart_page_renders_over_http(): void
    {
        $this->get('/cart')->assertOk();
    }

    public function test_wishlist_page_renders_over_http(): void
    {
        $this->get('/wishlist')->assertOk();
    }

    public function test_checkout_page_renders_over_http_for_authenticated_customer(): void
    {
        $customer = \App\Models\Customer::factory()->create();

        $this->actingAs($customer->user)->get('/checkout')->assertOk();
    }
}
