<?php

namespace Tests\Feature\Admin;

use App\Enums\PaymentMethod;
use App\Models\Address;
use App\Models\Customer;
use App\Models\DeliveryZone;
use App\Models\Inventory;
use App\Models\Order;
use App\Models\Product;
use App\Models\ProductVariant;
use App\Models\User;
use App\Models\Warehouse;
use App\Services\CartService;
use App\Services\CheckoutService;
use Database\Seeders\RolesAndPermissionsSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AdminDashboardAccessTest extends TestCase
{
    use RefreshDatabase;

    private function placedOrder(): Order
    {
        $warehouse = Warehouse::factory()->create(['is_active' => true]);
        $product = Product::factory()->create(['selling_price' => 100, 'mrp' => 120]);
        $variant = ProductVariant::factory()->default()->create(['product_id' => $product->id, 'selling_price' => 100, 'mrp' => 120]);
        Inventory::create(['warehouse_id' => $warehouse->id, 'product_variant_id' => $variant->id, 'quantity_on_hand' => 10]);

        $customer = Customer::factory()->create();
        $address = Address::factory()->default()->create(['customer_id' => $customer->id]);
        $zone = DeliveryZone::factory()->create();

        $cartService = app(CartService::class);
        $cart = $cartService->getOrCreateCart($customer, 'dashboard-test');
        $cartService->addItem($cart, $variant, 1);

        return app(CheckoutService::class)->placeOrder(
            $customer, $cart->fresh('items.variant.product'), $address, $zone, null, PaymentMethod::Cod,
        );
    }

    private function userWithRole(string $role): User
    {
        $this->seed(RolesAndPermissionsSeeder::class);
        $user = User::factory()->create();
        $user->assignRole($role);

        return $user;
    }

    public function test_dashboard_hides_orders_and_revenue_from_roles_without_those_permissions(): void
    {
        $order = $this->placedOrder();

        $this->actingAs($this->userWithRole('Delivery Agent'))->get(route('admin.dashboard'))
            ->assertOk()
            ->assertDontSee($order->order_number)
            ->assertDontSee('Total Revenue')
            ->assertDontSee('Recent Orders');
    }

    public function test_dashboard_shows_orders_but_not_revenue_to_order_managers_without_reports_access(): void
    {
        $order = $this->placedOrder();
        $user = $this->userWithRole('Delivery Manager');

        $this->actingAs($user)->get(route('admin.dashboard'))
            ->assertOk()
            ->assertSee($order->order_number)
            ->assertDontSee('Total Revenue');
    }

    public function test_super_admin_sees_everything_on_the_dashboard(): void
    {
        $order = $this->placedOrder();

        $this->actingAs($this->userWithRole('Super Admin'))->get(route('admin.dashboard'))
            ->assertOk()
            ->assertSee($order->order_number)
            ->assertSee('Total Revenue')
            ->assertSee('Low Stock Alerts');
    }
}
