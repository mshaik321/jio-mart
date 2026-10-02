<?php

namespace Tests\Feature\Admin;

use App\Models\Brand;
use App\Models\Category;
use App\Models\Customer;
use App\Models\Product;
use App\Models\Unit;
use App\Models\User;
use Database\Seeders\RolesAndPermissionsSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CatalogManagementTest extends TestCase
{
    use RefreshDatabase;

    private function admin(): User
    {
        $this->seed(RolesAndPermissionsSeeder::class);
        $user = User::factory()->create();
        $user->assignRole('Admin');

        return $user;
    }

    public function test_guest_is_redirected_from_admin_area(): void
    {
        $this->get('/admin')->assertRedirect('/login');
    }

    public function test_plain_customer_is_forbidden_from_admin_area(): void
    {
        $this->seed(RolesAndPermissionsSeeder::class);
        $customer = Customer::factory()->create();
        $customer->user->assignRole('Customer');

        $this->actingAs($customer->user)->get('/admin')->assertForbidden();
    }

    public function test_admin_can_view_dashboard(): void
    {
        $this->actingAs($this->admin())->get('/admin')->assertOk()->assertSee('Dashboard');
    }

    public function test_admin_can_create_and_update_a_category(): void
    {
        $admin = $this->admin();

        $this->actingAs($admin)->post('/admin/categories', [
            'name' => 'Beverages',
            'is_active' => true,
            'sort_order' => 1,
        ])->assertRedirect('/admin/categories');

        $category = Category::where('name', 'Beverages')->first();
        $this->assertNotNull($category);

        $this->actingAs($admin)->put("/admin/categories/{$category->id}", [
            'name' => 'Beverages & Drinks',
            'is_active' => true,
        ])->assertRedirect('/admin/categories');

        $this->assertSame('Beverages & Drinks', $category->fresh()->name);
    }

    public function test_admin_can_create_product_and_add_variant_with_stock(): void
    {
        $admin = $this->admin();
        $category = Category::factory()->create();
        $brand = Brand::factory()->create();
        $unit = Unit::factory()->create();
        \App\Models\Warehouse::factory()->create(['is_active' => true]);

        $response = $this->actingAs($admin)->post('/admin/products', [
            'category_id' => $category->id,
            'brand_id' => $brand->id,
            'unit_id' => $unit->id,
            'sku' => 'TESTSKU1',
            'name' => 'Test Product',
            'mrp' => 100,
            'selling_price' => 90,
            'status' => 'active',
        ]);

        $product = Product::where('sku', 'TESTSKU1')->first();
        $this->assertNotNull($product);
        $response->assertRedirect(route('admin.products.edit', $product));

        $this->actingAs($admin)->post(route('admin.products.variants.store', $product), [
            'sku' => 'TESTSKU1-1KG',
            'variant_name' => '1 KG',
            'mrp' => 100,
            'selling_price' => 90,
            'is_default' => true,
            'initial_quantity' => 25,
        ])->assertRedirect(route('admin.products.edit', $product));

        $variant = $product->variants()->first();
        $this->assertSame(25, $variant->totalStock());
    }

    public function test_delivery_agent_cannot_delete_products(): void
    {
        $this->seed(RolesAndPermissionsSeeder::class);
        $user = User::factory()->create();
        $user->assignRole('Delivery Agent');
        $product = Product::factory()->create();

        $this->actingAs($user)->delete("/admin/products/{$product->id}")->assertForbidden();
    }
}
