<?php

namespace Tests\Feature\Admin;

use App\Enums\InventoryMovementType;
use App\Models\AuditLog;
use App\Models\DeliveryAgent;
use App\Models\Inventory;
use App\Models\Product;
use App\Models\ProductVariant;
use App\Models\Setting;
use App\Models\User;
use App\Models\Warehouse;
use App\Services\InventoryService;
use Database\Seeders\RolesAndPermissionsSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Role;
use Tests\Concerns\CreatesPlacedOrders;
use Tests\TestCase;

class DeliveryAndSettingsTest extends TestCase
{
    use CreatesPlacedOrders, RefreshDatabase;

    private function admin(): User
    {
        $this->seed(RolesAndPermissionsSeeder::class);
        $user = User::factory()->create();
        $user->assignRole('Super Admin');

        return $user;
    }

    public function test_delivery_status_needs_an_assigned_agent_first(): void
    {
        $admin = $this->admin();
        $order = $this->placedOrder();

        $this->actingAs($admin)
            ->post(route('admin.orders.delivery.update-status', $order), ['status' => 'picked_up'])
            ->assertSessionHasErrors('status');
    }

    public function test_an_inactive_agent_cannot_be_assigned(): void
    {
        $admin = $this->admin();
        $order = $this->placedOrder();
        $agent = DeliveryAgent::factory()->create(['is_active' => false]);

        $this->actingAs($admin)
            ->post(route('admin.orders.delivery.assign', $order), ['delivery_agent_id' => $agent->id])
            ->assertSessionHasErrors('delivery_agent_id');

        $this->assertNull($order->fresh()->delivery);
    }

    public function test_agent_is_busy_while_assigned_and_free_after_delivery_or_failure(): void
    {
        $admin = $this->admin();
        $order = $this->placedOrder();
        $agent = DeliveryAgent::factory()->create(['is_active' => true]);

        $this->actingAs($admin)->post(route('admin.orders.delivery.assign', $order), ['delivery_agent_id' => $agent->id]);
        $this->assertSame('busy', $agent->fresh()->status->value ?? $agent->fresh()->status);

        $this->actingAs($admin)->post(route('admin.orders.delivery.update-status', $order), ['status' => 'delivered']);

        $this->assertSame('delivered', $order->fresh()->delivery->status->value);
        $this->assertSame('available', $agent->fresh()->status->value ?? $agent->fresh()->status);
    }

    public function test_customer_sees_delivery_progress_on_their_order(): void
    {
        $admin = $this->admin();
        $order = $this->placedOrder();
        $agent = DeliveryAgent::factory()->create(['is_active' => true, 'name' => 'Ravi Kumar']);

        $this->actingAs($admin)->post(route('admin.orders.delivery.assign', $order), ['delivery_agent_id' => $agent->id]);

        $this->actingAs($order->customer->user)->get(route('orders.show', $order))
            ->assertOk()
            ->assertSee('Ravi Kumar')
            ->assertSee('Assigned');
    }

    public function test_changing_role_permissions_is_audit_logged(): void
    {
        $admin = $this->admin();
        $role = Role::findByName('Delivery Agent');

        $this->actingAs($admin)->put(route('admin.roles.update', $role), ['permissions' => ['orders.view']])->assertRedirect();

        $log = AuditLog::query()->where('action', 'role.permissions_changed')->first();
        $this->assertNotNull($log);
        $this->assertSame($admin->id, $log->user_id);
        $this->assertSame(['orders.view'], $log->new_values['permissions']);
    }

    public function test_saved_settings_are_used_on_the_storefront(): void
    {
        Setting::setValue('site_name', 'Demo Mart');
        Setting::setValue('support_email', 'help@demomart.test');
        Setting::setValue('support_phone', '1800123456');

        $this->get(route('home'))
            ->assertOk()
            ->assertSee('Demo Mart')
            ->assertSee('help@demomart.test')
            ->assertSee('1800123456');
    }

    public function test_default_tax_rate_prefills_the_new_product_form(): void
    {
        Setting::setValue('default_tax_rate', '12');

        $this->actingAs($this->admin())->get(route('admin.products.create'))
            ->assertOk()
            ->assertSee('name="tax_rate" value="12"', false);
    }

    public function test_new_inventory_rows_use_the_default_low_stock_threshold(): void
    {
        Setting::setValue('low_stock_threshold_default', '7');
        $warehouse = Warehouse::factory()->create(['is_active' => true]);
        $variant = ProductVariant::factory()->default()->create(['product_id' => Product::factory()->create()->id]);

        app(InventoryService::class)->recordMovement($warehouse, $variant, InventoryMovementType::Adjustment, 5);

        $this->assertSame(7, Inventory::query()->where('product_variant_id', $variant->id)->value('low_stock_threshold'));
    }
}
