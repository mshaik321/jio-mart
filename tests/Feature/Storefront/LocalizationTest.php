<?php

namespace Tests\Feature\Storefront;

use App\Enums\ProductStatus;
use App\Models\Inventory;
use App\Models\Product;
use App\Models\ProductVariant;
use App\Models\Warehouse;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Volt\Volt;
use Tests\TestCase;

class LocalizationTest extends TestCase
{
    use RefreshDatabase;

    private function groundnuts(): Product
    {
        $product = Product::factory()->create([
            'name' => 'Groundnuts', 'slug' => 'groundnuts', 'status' => ProductStatus::Active, 'is_featured' => true,
            'short_description' => 'Raw groundnut kernels.',
        ]);
        $variant = ProductVariant::factory()->default()->create(['product_id' => $product->id]);
        Inventory::create(['warehouse_id' => Warehouse::factory()->create()->id, 'product_variant_id' => $variant->id, 'quantity_on_hand' => 30]);

        $product->translations()->create(['locale' => 'te', 'name' => 'వేరుశెనగలు', 'short_description' => 'పచ్చి వేరుశెనగ గింజలు.']);
        $product->category->translations()->create(['locale' => 'te', 'name' => 'నట్స్']);

        return $product;
    }

    public function test_storefront_defaults_to_english_and_shows_the_telugu_name_as_secondary(): void
    {
        $this->groundnuts();

        $this->get('/')
            ->assertOk()
            ->assertSee('<html lang="en">', false)
            ->assertSee('Shop by Category')
            ->assertSeeInOrder(['Groundnuts', 'వేరుశెనగలు'])
            ->assertSee('Add to Cart');
    }

    public function test_switching_to_telugu_translates_the_interface_and_catalog(): void
    {
        $product = $this->groundnuts();

        $this->get(route('locale.update', 'te'))->assertRedirect(route('home'))->assertSessionHas('locale', 'te');

        $this->get('/')
            ->assertOk()
            ->assertSee('<html lang="te">', false)
            ->assertSee('వర్గాల వారీగా కొనండి')
            ->assertSee('కార్ట్‌కు జోడించు')
            ->assertSee('నట్స్')
            ->assertSeeInOrder(['వేరుశెనగలు', 'Groundnuts']);

        $this->get(route('products.show', $product->slug))
            ->assertOk()
            ->assertSee('పచ్చి వేరుశెనగ గింజలు.')
            ->assertDontSee('Raw groundnut kernels.');
    }

    public function test_untranslated_products_fall_back_to_english_in_telugu_mode(): void
    {
        $product = Product::factory()->create(['name' => 'Chia Seeds', 'status' => ProductStatus::Active, 'is_featured' => true]);
        ProductVariant::factory()->default()->create(['product_id' => $product->id]);

        $this->withSession(['locale' => 'te'])->get('/')->assertOk()->assertSee('Chia Seeds');
    }

    public function test_unsupported_locales_are_rejected(): void
    {
        $this->get('/language/fr')->assertNotFound()->assertSessionMissing('locale');
    }

    public function test_language_switch_returns_to_the_previous_page_but_never_off_site(): void
    {
        $this->get('/language/te', ['Referer' => url('/search?q=dal')])->assertRedirect(url('/search?q=dal'));

        $this->get('/language/en', ['Referer' => 'https://evil.example/phish'])->assertRedirect(route('home'));
    }

    public function test_validation_messages_follow_the_chosen_language(): void
    {
        app()->setLocale('te');

        Volt::test('pages.auth.register')
            ->call('register')
            ->assertSee('మొబైల్ నంబర్ తప్పనిసరి.');
    }

    public function test_login_page_is_available_in_telugu(): void
    {
        $this->withSession(['locale' => 'te'])
            ->get('/login')
            ->assertOk()
            ->assertDontSee('Google తో కొనసాగండి')
            ->assertSee('పాస్‌వర్డ్ మర్చిపోయారా?')
            ->assertSee('ఖాతా సృష్టించండి');
    }

    public function test_api_returns_english_and_telugu_names_separately(): void
    {
        $this->groundnuts();

        $this->getJson('/api/v1/products', ['Accept-Language' => 'te'])
            ->assertOk()
            ->assertJsonPath('data.0.name', 'Groundnuts')
            ->assertJsonPath('data.0.localized_name', 'వేరుశెనగలు')
            ->assertJsonPath('data.0.translations.te.name', 'వేరుశెనగలు');

        $this->getJson('/api/v1/products?q='.urlencode('వేరుశెనగ'))
            ->assertOk()
            ->assertJsonPath('data.0.localized_name', 'Groundnuts')
            ->assertJsonCount(1, 'data');
    }
}
