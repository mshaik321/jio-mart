<?php

namespace Tests\Concerns;

use App\Enums\PaymentMethod;
use App\Models\Address;
use App\Models\Customer;
use App\Models\DeliveryZone;
use App\Models\Inventory;
use App\Models\Order;
use App\Models\Product;
use App\Models\ProductVariant;
use App\Models\Warehouse;
use App\Services\CartService;
use App\Services\CheckoutService;

trait CreatesPlacedOrders
{
    protected function placedOrder(): Order
    {
        $warehouse = Warehouse::query()->where('is_active', true)->first() ?? Warehouse::factory()->create(['is_active' => true]);
        $product = Product::factory()->create(['selling_price' => 100, 'mrp' => 120]);
        $variant = ProductVariant::factory()->default()->create(['product_id' => $product->id, 'selling_price' => 100, 'mrp' => 120]);
        Inventory::create(['warehouse_id' => $warehouse->id, 'product_variant_id' => $variant->id, 'quantity_on_hand' => 10]);

        $customer = Customer::factory()->create();
        $address = Address::factory()->default()->create(['customer_id' => $customer->id]);
        $zone = DeliveryZone::factory()->create();

        $cartService = app(CartService::class);
        $cart = $cartService->getOrCreateCart($customer, 'placed-'.$product->id);
        $cartService->addItem($cart, $variant, 1);

        return app(CheckoutService::class)->placeOrder(
            $customer, $cart->fresh('items.variant.product'), $address, $zone, null, PaymentMethod::Cod,
        );
    }
}
