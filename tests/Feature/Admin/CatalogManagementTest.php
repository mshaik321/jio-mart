<?php

namespace Tests\Feature\Admin;

use App\Models\Brand;
use App\Models\Category;
use App\Models\Customer;
use App\Models\Product;
use App\Models\Unit;
use App\Models\User;
use App\Models\Warehouse;
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
        Warehouse::factory()->create(['is_active' => true]);

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

    public function test_admin_can_save_telugu_product_copy_and_sort_order(): void
    {
        $admin = $this->admin();
        $product = Product::factory()->create(['status' => 'active', 'is_featured' => true]);

        $this->actingAs($admin)->put(route('admin.products.update', $product), [
            'category_id' => $product->category_id,
            'unit_id' => $product->unit_id,
            'sku' => $product->sku,
            'name' => 'Toor Dal',
            'mrp' => 185,
            'selling_price' => 165,
            'status' => 'active',
            'sort_order' => 3,
            'translations' => ['te' => ['name' => 'కందిపప్పు', 'short_description' => 'రోజువారీ పప్పుకి.', 'description' => '']],
        ])->assertRedirect(route('admin.products.edit', $product));

        $product->refresh()->load('translations');
        $this->assertSame('కందిపప్పు', $product->translationFor('name', 'te'));
        $this->assertSame('రోజువారీ పప్పుకి.', $product->translationFor('short_description', 'te'));
        $this->assertNull($product->translationFor('description', 'te'));
        $this->assertSame(3, $product->sort_order);
        $this->assertFalse($product->is_featured, 'An unchecked flag must switch featured off.');

        $this->actingAs($admin)->get(route('admin.products.edit', $product))
            ->assertOk()
            ->assertSee('value="కందిపప్పు"', false)
            ->assertSee('<option value="active" selected', false);
    }

    public function test_clearing_the_telugu_name_removes_the_translation(): void
    {
        $admin = $this->admin();
        $category = Category::factory()->create();
        $category->translations()->create(['locale' => 'te', 'name' => 'పప్పులు']);

        $this->actingAs($admin)->put("/admin/categories/{$category->id}", [
            'name' => $category->name,
            'translations' => ['te' => ['name' => '', 'description' => '']],
        ])->assertRedirect('/admin/categories');

        $this->assertSame(0, $category->translations()->count());
        $this->assertFalse($category->fresh()->is_active, 'An unchecked "Active" box must deactivate the category.');
    }

    public function test_translations_for_unsupported_locales_are_rejected(): void
    {
        $admin = $this->admin();
        $product = Product::factory()->create();

        $this->actingAs($admin)->put(route('admin.products.update', $product), [
            'category_id' => $product->category_id,
            'unit_id' => $product->unit_id,
            'sku' => $product->sku,
            'name' => $product->name,
            'mrp' => 10,
            'selling_price' => 9,
            'status' => 'active',
            'translations' => ['fr' => ['name' => 'Lentilles']],
        ])->assertSessionHasErrors('translations');
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
