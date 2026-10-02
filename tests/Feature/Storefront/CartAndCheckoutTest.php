<?php

namespace Tests\Feature\Storefront;

use App\Enums\ProductStatus;
use App\Livewire\Storefront\AddToCartButton;
use App\Livewire\Storefront\CartPage;
use App\Livewire\Storefront\CheckoutPage;
use App\Models\Address;
use App\Models\Customer;
use App\Models\DeliveryZone;
use App\Models\Inventory;
use App\Models\Product;
use App\Models\ProductVariant;
use App\Models\Warehouse;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class CartAndCheckoutTest extends TestCase
{
    use RefreshDatabase;

    private function makeStockedVariant(int $qty = 20): ProductVariant
    {
        $warehouse = Warehouse::factory()->create(['is_active' => true]);
        $product = Product::factory()->create(['status' => ProductStatus::Active, 'selling_price' => 50, 'mrp' => 60]);
        $variant = ProductVariant::factory()->default()->create([
            'product_id' => $product->id, 'selling_price' => 50, 'mrp' => 60,
        ]);

        Inventory::create([
            'warehouse_id' => $warehouse->id,
            'product_variant_id' => $variant->id,
            'quantity_on_hand' => $qty,
        ]);

        return $variant;
    }

    public function test_guest_can_add_item_to_cart_and_see_it(): void
    {
        $variant = $this->makeStockedVariant();

        Livewire::test(AddToCartButton::class, ['variant' => $variant])
            ->call('add')
            ->assertSet('added', true);

        Livewire::test(CartPage::class)
            ->assertSee($variant->product->name)
            ->assertSee('1');
    }

    public function test_customer_can_update_cart_quantity(): void
    {
        $customer = Customer::factory()->create();
        $this->actingAs($customer->user);

        $variant = $this->makeStockedVariant();

        Livewire::test(AddToCartButton::class, ['variant' => $variant])->call('add');

        Livewire::test(CartPage::class)
            ->call('updateQuantity', $variant->id, 3)
            ->assertSee('150.00'); // 3 * 50
    }

    public function test_customer_can_complete_checkout_end_to_end(): void
    {
        $customer = Customer::factory()->create();
        $this->actingAs($customer->user);

        $address = Address::factory()->default()->create(['customer_id' => $customer->id, 'pincode' => '110001']);
        DeliveryZone::factory()->create(['pincodes' => ['110001'], 'delivery_charge' => 20, 'free_delivery_above' => 1000]);

        $variant = $this->makeStockedVariant();

        Livewire::test(AddToCartButton::class, ['variant' => $variant])->call('add');

        $component = Livewire::test(CheckoutPage::class)
            ->set('addressId', $address->id)
            ->call('placeOrder');

        $component->assertHasNoErrors();

        $this->assertDatabaseHas('orders', [
            'customer_id' => $customer->id,
            'status' => 'pending',
        ]);

        $this->assertDatabaseCount('cart_items', 0);
    }

    public function test_checkout_shows_error_when_pincode_not_serviced(): void
    {
        $customer = Customer::factory()->create();
        $this->actingAs($customer->user);

        $address = Address::factory()->default()->create(['customer_id' => $customer->id, 'pincode' => '999999']);
        DeliveryZone::factory()->create(['pincodes' => ['110001']]);

        $variant = $this->makeStockedVariant();
        Livewire::test(AddToCartButton::class, ['variant' => $variant])->call('add');

        Livewire::test(CheckoutPage::class)
            ->set('addressId', $address->id)
            ->call('placeOrder')
            ->assertSet('error', fn ($value) => str_contains($value, 'do not deliver'));
    }
}
