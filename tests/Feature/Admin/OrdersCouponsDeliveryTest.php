<?php

namespace Tests\Feature\Admin;

use App\Enums\OrderStatus;
use App\Enums\PaymentMethod;
use App\Models\Address;
use App\Models\Customer;
use App\Models\DeliveryAgent;
use App\Models\DeliveryZone;
use App\Models\Inventory;
use App\Models\Product;
use App\Models\ProductVariant;
use App\Models\User;
use App\Models\Warehouse;
use App\Services\CartService;
use App\Services\CheckoutService;
use Database\Seeders\RolesAndPermissionsSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class OrdersCouponsDeliveryTest extends TestCase
{
    use RefreshDatabase;

    private function admin(): User
    {
        $this->seed(RolesAndPermissionsSeeder::class);
        $user = User::factory()->create();
        $user->assignRole('Admin');

        return $user;
    }

    private function placedOrder()
    {
        $warehouse = Warehouse::factory()->create(['is_active' => true]);
        $product = Product::factory()->create(['selling_price' => 100, 'mrp' => 120]);
        $variant = ProductVariant::factory()->default()->create(['product_id' => $product->id, 'selling_price' => 100, 'mrp' => 120]);
        Inventory::create(['warehouse_id' => $warehouse->id, 'product_variant_id' => $variant->id, 'quantity_on_hand' => 10]);

        $customer = Customer::factory()->create();
        $address = Address::factory()->default()->create(['customer_id' => $customer->id]);
        $zone = DeliveryZone::factory()->create();

        $cartService = app(CartService::class);
        $cart = $cartService->getOrCreateCart($customer, 'admin-test-session');
        $cartService->addItem($cart, $variant, 1);

        return app(CheckoutService::class)->placeOrder(
            $customer, $cart->fresh('items.variant.product'), $address, $zone, null, PaymentMethod::Cod,
        );
    }

    public function test_admin_can_view_order_and_update_status(): void
    {
        $admin = $this->admin();
        $order = $this->placedOrder();

        $this->actingAs($admin)->get(route('admin.orders.show', $order))->assertOk()->assertSee($order->order_number);

        $this->actingAs($admin)->post(route('admin.orders.update-status', $order), [
            'status' => 'confirmed',
            'note' => 'Verified payment',
        ])->assertRedirect();

        $this->assertSame(OrderStatus::Confirmed, $order->fresh()->status);
    }

    public function test_admin_can_assign_delivery_agent(): void
    {
        $admin = $this->admin();
        $order = $this->placedOrder();
        $agent = DeliveryAgent::factory()->create(['is_active' => true]);

        $this->actingAs($admin)->post(route('admin.orders.delivery.assign', $order), [
            'delivery_agent_id' => $agent->id,
        ])->assertRedirect();

        $this->assertSame($agent->id, $order->fresh()->delivery->delivery_agent_id);
        $this->assertSame('assigned', $order->fresh()->delivery->status->value);
    }

    public function test_admin_can_manage_coupons(): void
    {
        $admin = $this->admin();

        $this->actingAs($admin)->post('/admin/coupons', [
            'code' => 'SAVE20',
            'type' => 'percentage',
            'value' => 20,
            'min_order_amount' => 100,
            'per_customer_limit' => 1,
            'is_active' => true,
        ])->assertRedirect('/admin/coupons');

        $this->assertDatabaseHas('coupons', ['code' => 'SAVE20']);
    }

    public function test_admin_can_manage_delivery_zones_slots_and_agents(): void
    {
        $admin = $this->admin();

        $this->actingAs($admin)->post('/admin/delivery-zones', [
            'name' => 'North Zone',
            'pincodes' => '110001, 110002',
            'delivery_charge' => 30,
            'is_active' => true,
        ])->assertRedirect('/admin/delivery-zones');

        $this->assertDatabaseHas('delivery_zones', ['name' => 'North Zone']);

        $this->actingAs($admin)->post('/admin/delivery-slots', [
            'name' => 'Morning',
            'start_time' => '08:00',
            'end_time' => '11:00',
            'max_orders' => 50,
            'is_active' => true,
        ])->assertRedirect('/admin/delivery-slots');

        $this->assertDatabaseHas('delivery_slots', ['name' => 'Morning']);

        $this->actingAs($admin)->post('/admin/delivery-agents', [
            'name' => 'Raju Kumar',
            'phone' => '9988776655',
            'is_active' => true,
        ])->assertRedirect('/admin/delivery-agents');

        $this->assertDatabaseHas('delivery_agents', ['name' => 'Raju Kumar']);
    }
}
