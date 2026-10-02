<?php

namespace Tests\Feature\Admin;

use App\Models\ProductVariant;
use App\Models\Supplier;
use App\Models\User;
use App\Models\Warehouse;
use Database\Seeders\RolesAndPermissionsSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class InventoryAndPurchasingTest extends TestCase
{
    use RefreshDatabase;

    private function admin(): User
    {
        $this->seed(RolesAndPermissionsSeeder::class);
        $user = User::factory()->create();
        $user->assignRole('Admin');

        return $user;
    }

    public function test_admin_can_manually_adjust_stock(): void
    {
        $admin = $this->admin();
        $warehouse = Warehouse::factory()->create();
        $variant = ProductVariant::factory()->create();

        $this->actingAs($admin)->post('/admin/inventory/adjust', [
            'warehouse_id' => $warehouse->id,
            'product_variant_id' => $variant->id,
            'quantity' => 15,
            'note' => 'Stock take correction',
        ])->assertRedirect('/admin/inventory');

        $this->assertSame(15, $variant->fresh()->totalStock());
        $this->assertDatabaseHas('inventory_movements', [
            'product_variant_id' => $variant->id,
            'quantity' => 15,
            'type' => 'adjustment',
        ]);
    }

    public function test_admin_can_view_inventory_index(): void
    {
        $this->actingAs($this->admin())->get('/admin/inventory')->assertOk()->assertSee('Inventory');
    }

    public function test_admin_can_create_purchase_order_and_receive_stock(): void
    {
        $admin = $this->admin();
        $supplier = Supplier::factory()->create();
        $warehouse = Warehouse::factory()->create();
        $variant = ProductVariant::factory()->create();

        $response = $this->actingAs($admin)->post('/admin/purchase-orders', [
            'supplier_id' => $supplier->id,
            'warehouse_id' => $warehouse->id,
            'order_date' => now()->format('Y-m-d'),
            'items' => [
                ['product_variant_id' => $variant->id, 'quantity_ordered' => 50, 'unit_cost' => 20],
            ],
        ]);

        $po = $supplier->purchaseOrders()->first();
        $this->assertNotNull($po);
        $response->assertRedirect(route('admin.purchase-orders.show', $po));

        $this->actingAs($admin)->get(route('admin.purchase-orders.show', $po))->assertOk();

        $this->actingAs($admin)->post(route('admin.purchase-orders.receive', $po), [
            'receive' => [$po->items->first()->id => 30],
        ])->assertRedirect(route('admin.purchase-orders.show', $po));

        $this->assertSame(30, $variant->fresh()->totalStock());
        $this->assertSame('partially_received', $po->fresh()->status->value);
    }
}
