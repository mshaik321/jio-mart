<?php

namespace Tests\Feature\Storefront;

use App\Enums\ProductStatus;
use App\Models\Customer;
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
        $customer = Customer::factory()->create();

        $this->actingAs($customer->user)->get('/checkout')->assertOk();
    }

    public function test_home_page_lists_featured_products_with_image_names_unit_price_and_stock(): void
    {
        $variant = $this->makeStockedVariant(['name' => 'Toor Dal', 'slug' => 'toor-dal', 'is_featured' => true]);
        $variant->update(['variant_name' => '1 kg', 'selling_price' => 165, 'mrp' => 185]);
        $variant->product->translations()->create(['locale' => 'te', 'name' => 'కందిపప్పు']);
        $variant->product->images()->create(['path' => 'catalog/products/toor-dal.webp', 'is_primary' => true]);

        $this->get('/')
            ->assertOk()
            ->assertSee('Featured Products')
            ->assertSee('Toor Dal')
            ->assertSee('కందిపప్పు')
            ->assertSee('/storage/catalog/products/toor-dal.webp')
            ->assertSee('1 kg')
            ->assertSee('₹165.00', false)
            ->assertSee('11% OFF')
            ->assertSee('In stock')
            ->assertSee('Add to Cart');
    }

    public function test_category_page_only_lists_products_from_that_category(): void
    {
        $dal = $this->makeStockedVariant(['name' => 'Moong Dal', 'slug' => 'moong-dal']);
        $this->makeStockedVariant(['name' => 'Sunflower Oil', 'slug' => 'sunflower-oil']);

        $this->get(route('categories.show', $dal->product->category->slug))
            ->assertOk()
            ->assertSee('Moong Dal')
            ->assertDontSee('Sunflower Oil');
    }

    public function test_search_matches_products_by_their_telugu_name(): void
    {
        $variant = $this->makeStockedVariant(['name' => 'Groundnuts', 'slug' => 'groundnuts']);
        $variant->product->translations()->create(['locale' => 'te', 'name' => 'వేరుశెనగలు']);
        $this->makeStockedVariant(['name' => 'Cashews', 'slug' => 'cashews']);

        $this->get(route('search', ['q' => 'వేరుశెనగ']))
            ->assertOk()
            ->assertSee('Groundnuts')
            ->assertDontSee('Cashews');
    }

    public function test_inactive_products_are_not_reachable_from_the_storefront(): void
    {
        $variant = $this->makeStockedVariant(['name' => 'Hidden Draft Product', 'status' => ProductStatus::Draft]);

        $this->get(route('products.show', $variant->product->slug))->assertNotFound();
        $this->get(route('search', ['q' => 'Hidden Draft']))->assertOk()->assertDontSee('Hidden Draft Product');
    }

    public function test_inactive_category_returns_404(): void
    {
        $variant = $this->makeStockedVariant();
        $variant->product->category->update(['is_active' => false]);

        $this->get(route('categories.show', $variant->product->category->slug))->assertNotFound();
    }

    public function test_out_of_stock_products_show_status_and_cannot_be_added_to_cart(): void
    {
        $product = Product::factory()->create(['name' => 'Dried Cranberries', 'status' => ProductStatus::Active, 'is_featured' => true]);
        ProductVariant::factory()->default()->create(['product_id' => $product->id]);

        $this->get('/')
            ->assertOk()
            ->assertSee('Dried Cranberries')
            ->assertSee('Out of stock')
            ->assertDontSee('Add to Cart');

        $this->get(route('products.show', $product->slug))
            ->assertOk()
            ->assertSee('Out of stock')
            ->assertDontSeeLivewire('storefront.add-to-cart-button');
    }

    public function test_low_stock_products_show_how_many_are_left(): void
    {
        $variant = $this->makeStockedVariant(['is_featured' => true]);
        $variant->inventories()->update(['quantity_on_hand' => 4]);

        $this->get('/')->assertOk()->assertSee('Only 4 left');
    }
}
