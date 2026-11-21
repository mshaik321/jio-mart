<?php

namespace Tests\Feature\Services;

use App\Enums\OrderStatus;
use App\Enums\PaymentMethod;
use App\Enums\PurchaseOrderStatus;
use App\Models\Address;
use App\Models\Customer;
use App\Models\DeliveryZone;
use App\Models\Inventory;
use App\Models\Product;
use App\Models\ProductVariant;
use App\Models\PurchaseOrder;
use App\Models\PurchaseOrderItem;
use App\Models\Supplier;
use App\Models\Warehouse;
use App\Services\CartService;
use App\Services\CheckoutService;
use App\Services\OrderService;
use App\Services\PurchaseService;
use App\Services\ReturnService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Validation\ValidationException;
use Tests\TestCase;

class OrderLifecycleTest extends TestCase
{
    use RefreshDatabase;

    private function makeVariantWithStock(int $qty = 50): ProductVariant
    {
        $warehouse = Warehouse::factory()->create(['is_active' => true]);
        $product = Product::factory()->create(['selling_price' => 100, 'mrp' => 120, 'tax_rate' => 0]);
        $variant = ProductVariant::factory()->default()->create([
            'product_id' => $product->id, 'selling_price' => 100, 'mrp' => 120,
        ]);

        Inventory::create([
            'warehouse_id' => $warehouse->id,
            'product_variant_id' => $variant->id,
            'quantity_on_hand' => $qty,
        ]);

        return $variant;
    }

    public function test_cancelling_an_order_restores_stock(): void
    {
        $variant = $this->makeVariantWithStock(10);
        $customer = Customer::factory()->create();
        $address = Address::factory()->default()->create(['customer_id' => $customer->id]);
        $zone = DeliveryZone::factory()->create();

        $cartService = app(CartService::class);
        $cart = $cartService->getOrCreateCart($customer, 'sess');
        $cartService->addItem($cart, $variant, 4);

        $order = app(CheckoutService::class)->placeOrder(
            $customer, $cart->fresh('items.variant.product'), $address, $zone, null, PaymentMethod::Cod,
        );

        $this->assertSame(6, $variant->fresh()->totalStock());

        app(OrderService::class)->cancel($order);

        $this->assertSame(OrderStatus::Cancelled, $order->fresh()->status);
        $this->assertSame(10, $variant->fresh()->totalStock());
    }

    public function test_cannot_cancel_a_delivered_order(): void
    {
        $variant = $this->makeVariantWithStock(10);
        $customer = Customer::factory()->create();
        $address = Address::factory()->default()->create(['customer_id' => $customer->id]);
        $zone = DeliveryZone::factory()->create();

        $cartService = app(CartService::class);
        $cart = $cartService->getOrCreateCart($customer, 'sess2');
        $cartService->addItem($cart, $variant, 1);

        $order = app(CheckoutService::class)->placeOrder(
            $customer, $cart->fresh('items.variant.product'), $address, $zone, null, PaymentMethod::Cod,
        );

        $orderService = app(OrderService::class);
        $orderService->updateStatus($order, OrderStatus::Confirmed);
        $orderService->updateStatus($order, OrderStatus::Processing);
        $orderService->updateStatus($order, OrderStatus::Packed);
        $orderService->updateStatus($order, OrderStatus::OutForDelivery);
        $orderService->updateStatus($order, OrderStatus::Delivered);

        $this->expectException(ValidationException::class);
        $orderService->cancel($order->fresh());
    }

    public function test_receiving_a_purchase_order_increases_stock_and_updates_status(): void
    {
        $warehouse = Warehouse::factory()->create();
        $supplier = Supplier::factory()->create();
        $variant = ProductVariant::factory()->create();

        $po = PurchaseOrder::create([
            'po_number' => 'PO-TEST-1',
            'supplier_id' => $supplier->id,
            'warehouse_id' => $warehouse->id,
            'status' => PurchaseOrderStatus::Ordered,
            'order_date' => now(),
        ]);

        $item = PurchaseOrderItem::create([
            'purchase_order_id' => $po->id,
            'product_variant_id' => $variant->id,
            'quantity_ordered' => 100,
            'unit_cost' => 50,
        ]);

        app(PurchaseService::class)->receive($po, [$item->id => 40]);

        $this->assertSame(40, $variant->fresh()->totalStock());
        $this->assertSame(PurchaseOrderStatus::PartiallyReceived, $po->fresh()->status);

        app(PurchaseService::class)->receive($po->fresh(), [$item->id => 60]);

        $this->assertSame(100, $variant->fresh()->totalStock());
        $this->assertSame(PurchaseOrderStatus::Received, $po->fresh()->status);
    }

    public function test_return_flow_restores_stock_after_delivered_order(): void
    {
        $variant = $this->makeVariantWithStock(10);
        $customer = Customer::factory()->create();
        $address = Address::factory()->default()->create(['customer_id' => $customer->id]);
        $zone = DeliveryZone::factory()->create();

        $cartService = app(CartService::class);
        $cart = $cartService->getOrCreateCart($customer, 'sess3');
        $cartService->addItem($cart, $variant, 2);

        $order = app(CheckoutService::class)->placeOrder(
            $customer, $cart->fresh('items.variant.product'), $address, $zone, null, PaymentMethod::Cod,
        );

        $orderService = app(OrderService::class);
        foreach ([OrderStatus::Confirmed, OrderStatus::Processing, OrderStatus::Packed, OrderStatus::OutForDelivery, OrderStatus::Delivered] as $status) {
            $orderService->updateStatus($order->fresh(), $status);
        }

        $this->assertSame(8, $variant->fresh()->totalStock());

        $orderItem = $order->items()->first();
        $return = app(ReturnService::class)->request($order->fresh(), [
            ['order_item_id' => $orderItem->id, 'quantity' => 2, 'reason' => 'Damaged'],
        ], 'Damaged on arrival');

        app(ReturnService::class)->approve($return);
        app(ReturnService::class)->complete($return->fresh(), 200.00);

        $this->assertSame(10, $variant->fresh()->totalStock());
        $this->assertSame('refunded', $order->fresh()->payment_status->value);
    }
}
