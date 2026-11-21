<?php

namespace Tests\Feature\Admin;

use App\Enums\OrderStatus;
use App\Enums\PaymentMethod;
use App\Models\Address;
use App\Models\Customer;
use App\Models\DeliveryZone;
use App\Models\Inventory;
use App\Models\Order;
use App\Models\OrderReturn;
use App\Models\Product;
use App\Models\ProductVariant;
use App\Models\User;
use App\Models\Warehouse;
use App\Services\CartService;
use App\Services\CheckoutService;
use App\Services\OrderService;
use App\Services\ReturnService;
use Database\Seeders\RolesAndPermissionsSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ReturnGuardsTest extends TestCase
{
    use RefreshDatabase;

    private ProductVariant $variant;

    private function deliveredOrder(?Customer $customer = null): Order
    {
        $warehouse = Warehouse::query()->where('is_active', true)->first() ?? Warehouse::factory()->create(['is_active' => true]);
        $product = Product::factory()->create(['selling_price' => 100, 'mrp' => 120]);
        $this->variant = ProductVariant::factory()->default()->create(['product_id' => $product->id, 'selling_price' => 100, 'mrp' => 120]);
        Inventory::create(['warehouse_id' => $warehouse->id, 'product_variant_id' => $this->variant->id, 'quantity_on_hand' => 10]);

        $customer ??= Customer::factory()->create();
        $address = Address::factory()->default()->create(['customer_id' => $customer->id]);
        $zone = DeliveryZone::factory()->create();

        $cartService = app(CartService::class);
        $cart = $cartService->getOrCreateCart($customer, 'guard-'.$product->id);
        $cartService->addItem($cart, $this->variant, 2);

        $order = app(CheckoutService::class)->placeOrder(
            $customer, $cart->fresh('items.variant.product'), $address, $zone, null, PaymentMethod::Cod,
        );

        foreach ([OrderStatus::Confirmed, OrderStatus::Processing, OrderStatus::Packed, OrderStatus::OutForDelivery, OrderStatus::Delivered] as $status) {
            app(OrderService::class)->updateStatus($order->fresh(), $status);
        }

        return $order->fresh();
    }

    private function admin(): User
    {
        $this->seed(RolesAndPermissionsSeeder::class);
        $user = User::factory()->create();
        $user->assignRole('Admin');

        return $user;
    }

    /**
     * @return array{0: Order, 1: OrderReturn}
     */
    private function requestedReturn(): array
    {
        $order = $this->deliveredOrder();
        $return = app(ReturnService::class)->request($order, [
            ['order_item_id' => $order->items->first()->id, 'quantity' => 2],
        ], 'Damaged');

        return [$order, $return];
    }

    public function test_customer_can_return_one_item_without_ticking_every_item(): void
    {
        $this->seed(RolesAndPermissionsSeeder::class);
        $order = $this->deliveredOrder();
        $first = $order->items->first();

        $this->actingAs($order->customer->user)
            ->post(route('orders.return.store', $order), [
                'reason' => 'Damaged',
                'items' => [
                    ['order_item_id' => $first->id, 'quantity' => 2],
                    ['quantity' => 5],
                ],
            ])
            ->assertSessionHasNoErrors()
            ->assertSessionHas('status');

        $this->assertSame(1, OrderReturn::count());
        $this->assertSame(1, OrderReturn::first()->items()->count());
    }

    public function test_return_quantity_cannot_exceed_quantity_purchased(): void
    {
        $order = $this->deliveredOrder();

        $this->actingAs($order->customer->user)
            ->post(route('orders.return.store', $order), [
                'reason' => 'Damaged',
                'items' => [['order_item_id' => $order->items->first()->id, 'quantity' => 50]],
            ])
            ->assertSessionHasErrors('items');

        $this->assertSame(0, OrderReturn::count());
    }

    public function test_return_cannot_include_an_item_from_another_order(): void
    {
        $order = $this->deliveredOrder();
        $other = $this->deliveredOrder();

        $this->actingAs($order->customer->user)
            ->post(route('orders.return.store', $order), [
                'reason' => 'Damaged',
                'items' => [['order_item_id' => $other->items->first()->id, 'quantity' => 1]],
            ])
            ->assertSessionHasErrors('items');

        $this->assertSame(0, OrderReturn::count());
    }

    public function test_an_order_can_only_have_one_return(): void
    {
        [$order] = $this->requestedReturn();

        $this->actingAs($order->customer->user)
            ->post(route('orders.return.store', $order), [
                'reason' => 'Again',
                'items' => [['order_item_id' => $order->items->first()->id, 'quantity' => 1]],
            ])
            ->assertSessionHasErrors('order');

        $this->assertSame(1, OrderReturn::count());
    }

    public function test_a_completed_return_cannot_be_completed_again(): void
    {
        [, $return] = $this->requestedReturn();
        $admin = $this->admin();

        $this->actingAs($admin)->post(route('admin.returns.approve', $return));
        $this->actingAs($admin)->post(route('admin.returns.complete', $return), ['refund_amount' => 100]);
        $stockAfterFirstCompletion = $this->variant->fresh()->totalStock();

        $this->actingAs($admin)->post(route('admin.returns.complete', $return), ['refund_amount' => 100])
            ->assertSessionHasErrors('return');

        $this->assertSame($stockAfterFirstCompletion, $this->variant->fresh()->totalStock());
    }

    public function test_a_return_must_be_approved_before_it_is_completed(): void
    {
        [, $return] = $this->requestedReturn();

        $this->actingAs($this->admin())->post(route('admin.returns.complete', $return), ['refund_amount' => 100])
            ->assertSessionHasErrors('return');

        $this->assertSame('requested', $return->fresh()->status->value);
    }

    public function test_a_rejected_return_cannot_be_approved_or_completed(): void
    {
        [, $return] = $this->requestedReturn();
        $admin = $this->admin();

        $this->actingAs($admin)->post(route('admin.returns.reject', $return), ['note' => 'No']);

        $this->actingAs($admin)->post(route('admin.returns.approve', $return))->assertSessionHasErrors('return');
        $this->actingAs($admin)->post(route('admin.returns.complete', $return), ['refund_amount' => 100])->assertSessionHasErrors('return');

        $this->assertSame('rejected', $return->fresh()->status->value);
    }

    public function test_refund_cannot_exceed_the_order_total(): void
    {
        [$order, $return] = $this->requestedReturn();
        $admin = $this->admin();
        $this->actingAs($admin)->post(route('admin.returns.approve', $return));

        $this->actingAs($admin)
            ->post(route('admin.returns.complete', $return), ['refund_amount' => (float) $order->grand_total + 1])
            ->assertSessionHasErrors('refund_amount');

        $this->assertSame('approved', $return->fresh()->status->value);
    }

    public function test_variant_selling_price_cannot_exceed_mrp(): void
    {
        $admin = $this->admin();
        $product = Product::factory()->create();

        $this->actingAs($admin)->post(route('admin.products.variants.store', $product), [
            'sku' => 'OVER-1', 'variant_name' => '1 KG', 'mrp' => 100, 'selling_price' => 500,
        ])->assertSessionHasErrors('selling_price');
    }

    public function test_admin_sidebar_only_lists_pages_the_role_can_open(): void
    {
        $this->seed(RolesAndPermissionsSeeder::class);
        $manager = User::factory()->create();
        $manager->assignRole('Order Manager');

        $this->actingAs($manager)->get(route('admin.dashboard'))
            ->assertOk()
            ->assertSee(route('admin.orders.index'), false)
            ->assertDontSee(route('admin.users.index'), false)
            ->assertDontSee(route('admin.settings.index'), false)
            ->assertDontSee(route('admin.categories.index'), false);

        $admin = $this->admin();
        $this->actingAs($admin)->get(route('admin.dashboard'))
            ->assertOk()
            ->assertDontSee(route('admin.roles.index'), false);
    }
}
