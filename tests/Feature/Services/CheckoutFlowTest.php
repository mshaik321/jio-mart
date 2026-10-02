<?php

namespace Tests\Feature\Services;

use App\Enums\OrderStatus;
use App\Enums\PaymentMethod;
use App\Models\Address;
use App\Models\Customer;
use App\Models\DeliveryZone;
use App\Models\Inventory;
use App\Models\Product;
use App\Models\ProductVariant;
use App\Models\Warehouse;
use App\Services\CartService;
use App\Services\CheckoutService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Validation\ValidationException;
use Tests\TestCase;

class CheckoutFlowTest extends TestCase
{
    use RefreshDatabase;

    private function makeVariantWithStock(int $qty = 50): ProductVariant
    {
        $warehouse = Warehouse::factory()->create(['is_active' => true]);
        $product = Product::factory()->create(['selling_price' => 100, 'mrp' => 120, 'tax_rate' => 10]);
        $variant = ProductVariant::factory()->default()->create([
            'product_id' => $product->id,
            'selling_price' => 100,
            'mrp' => 120,
        ]);

        Inventory::create([
            'warehouse_id' => $warehouse->id,
            'product_variant_id' => $variant->id,
            'quantity_on_hand' => $qty,
            'quantity_reserved' => 0,
        ]);

        return $variant;
    }

    public function test_customer_can_place_an_order_and_stock_is_decremented(): void
    {
        $variant = $this->makeVariantWithStock(50);
        $customer = Customer::factory()->create();
        $address = Address::factory()->default()->create(['customer_id' => $customer->id]);
        $zone = DeliveryZone::factory()->create(['delivery_charge' => 20, 'free_delivery_above' => 500]);

        $cartService = app(CartService::class);
        $cart = $cartService->getOrCreateCart($customer, 'session-1');
        $cartService->addItem($cart, $variant, 3);

        $order = app(CheckoutService::class)->placeOrder(
            $customer, $cart->fresh('items.variant.product'), $address, $zone, null, PaymentMethod::Cod,
        );

        $this->assertSame(OrderStatus::Pending, $order->status);
        $this->assertSame('300.00', $order->subtotal);
        $this->assertSame('30.00', $order->tax_amount);
        $this->assertSame('20.00', $order->delivery_charge);
        $this->assertSame('350.00', $order->grand_total);
        $this->assertCount(1, $order->items);
        $this->assertSame(0, $cart->items()->count());

        $variant->refresh();
        $this->assertSame(47, $variant->totalStock());
    }

    public function test_checkout_fails_when_stock_is_insufficient(): void
    {
        $variant = $this->makeVariantWithStock(2);
        $customer = Customer::factory()->create();
        $address = Address::factory()->default()->create(['customer_id' => $customer->id]);
        $zone = DeliveryZone::factory()->create();

        $cartService = app(CartService::class);
        $cart = $cartService->getOrCreateCart($customer, 'session-2');
        $cartService->addItem($cart, $variant, 2);

        // Someone else buys the remaining stock before checkout completes.
        $variant->inventories()->first()->update(['quantity_on_hand' => 1]);

        $this->expectException(ValidationException::class);

        app(CheckoutService::class)->placeOrder(
            $customer, $cart->fresh('items.variant.product'), $address, $zone, null, PaymentMethod::Cod,
        );
    }

    public function test_cart_rejects_adding_more_than_available_stock(): void
    {
        $variant = $this->makeVariantWithStock(5);
        $customer = Customer::factory()->create();
        $cartService = app(CartService::class);
        $cart = $cartService->getOrCreateCart($customer, 'session-3');

        $this->expectException(ValidationException::class);
        $cartService->addItem($cart, $variant, 999);
    }

    public function test_free_delivery_applies_above_threshold(): void
    {
        $variant = $this->makeVariantWithStock(50);
        $customer = Customer::factory()->create();
        $address = Address::factory()->default()->create(['customer_id' => $customer->id]);
        $zone = DeliveryZone::factory()->create(['delivery_charge' => 20, 'free_delivery_above' => 250]);

        $cartService = app(CartService::class);
        $cart = $cartService->getOrCreateCart($customer, 'session-4');
        $cartService->addItem($cart, $variant, 3); // subtotal 300 >= 250

        $order = app(CheckoutService::class)->placeOrder(
            $customer, $cart->fresh('items.variant.product'), $address, $zone, null, PaymentMethod::Cod,
        );

        $this->assertSame('0.00', $order->delivery_charge);
    }
}
