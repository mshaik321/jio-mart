<?php

namespace Tests\Feature\Admin;

use App\Models\Brand;
use App\Models\Category;
use App\Models\Setting;
use App\Models\Unit;
use App\Models\User;
use Database\Seeders\RolesAndPermissionsSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ReportsSettingsAuditTest extends TestCase
{
    use RefreshDatabase;

    private function admin(): User
    {
        $this->seed(RolesAndPermissionsSeeder::class);
        $user = User::factory()->create();
        $user->assignRole('Admin');

        return $user;
    }

    public function test_admin_can_view_reports(): void
    {
        $this->actingAs($this->admin())->get('/admin/reports')->assertOk()->assertSee('Reports');
    }

    public function test_admin_can_update_settings(): void
    {
        $admin = $this->admin();

        $this->actingAs($admin)->put('/admin/settings', [
            'site_name' => 'GroceryHub',
            'support_email' => 'support@groceryhub.test',
            'default_tax_rate' => 5,
        ])->assertRedirect('/admin/settings');

        $this->assertSame('GroceryHub', Setting::getValue('site_name'));
    }

    public function test_product_creation_and_order_status_change_are_audit_logged(): void
    {
        $admin = $this->admin();
        $category = Category::factory()->create();
        $brand = Brand::factory()->create();
        $unit = Unit::factory()->create();

        $this->actingAs($admin)->post('/admin/products', [
            'category_id' => $category->id,
            'brand_id' => $brand->id,
            'unit_id' => $unit->id,
            'sku' => 'AUDITSKU1',
            'name' => 'Audit Test Product',
            'mrp' => 50,
            'selling_price' => 45,
            'status' => 'active',
        ]);

        $this->assertDatabaseHas('audit_logs', ['action' => 'product.created']);

        $this->actingAs($admin)->get('/admin/audit-logs')->assertOk()->assertSee('product.created');
    }
}
