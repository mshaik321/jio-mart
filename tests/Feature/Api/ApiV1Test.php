<?php

namespace Tests\Feature\Api;

use App\Enums\ProductStatus;
use App\Models\Address;
use App\Models\Category;
use App\Models\Customer;
use App\Models\DeliveryZone;
use App\Models\Inventory;
use App\Models\Product;
use App\Models\ProductVariant;
use App\Models\Warehouse;
use Database\Seeders\RolesAndPermissionsSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ApiV1Test extends TestCase
{
    use RefreshDatabase;

    private function stockedVariant(): ProductVariant
    {
        $warehouse = Warehouse::factory()->create(['is_active' => true]);
        $product = Product::factory()->create(['status' => ProductStatus::Active, 'selling_price' => 80, 'mrp' => 100]);
        $variant = ProductVariant::factory()->default()->create(['product_id' => $product->id, 'selling_price' => 80, 'mrp' => 100]);
        Inventory::create(['warehouse_id' => $warehouse->id, 'product_variant_id' => $variant->id, 'quantity_on_hand' => 20]);

        return $variant;
    }

    public function test_user_can_register_and_receive_a_token(): void
    {
        $this->seed(RolesAndPermissionsSeeder::class);

        $response = $this->postJson('/api/v1/auth/register', [
            'name' => 'API Tester',
            'email' => 'apitester@groceryhub.test',
            'password' => 'Password123!',
        ]);

        $response->assertCreated()->assertJsonStructure(['token', 'user']);
        $this->assertDatabaseHas('customers', []);
    }

    public function test_user_can_login_and_access_profile(): void
    {
        $this->seed(RolesAndPermissionsSeeder::class);
        $customer = Customer::factory()->create();

        $login = $this->postJson('/api/v1/auth/login', [
            'email' => $customer->user->email,
            'password' => 'password',
        ])->assertOk();

        $token = $login->json('token');

        $this->withHeader('Authorization', "Bearer {$token}")
            ->getJson('/api/v1/profile')
            ->assertOk()
            ->assertJsonFragment(['email' => $customer->user->email]);
    }

    public function test_guest_can_browse_products_and_categories(): void
    {
        $variant = $this->stockedVariant();

        $this->getJson('/api/v1/categories')->assertOk();

        $this->getJson('/api/v1/products')
            ->assertOk()
            ->assertJsonFragment(['name' => $variant->product->name]);

        $this->getJson("/api/v1/products/{$variant->product->slug}")
            ->assertOk()
            ->assertJsonFragment(['sku' => $variant->product->sku]);
    }

    public function test_authenticated_user_can_manage_cart_and_checkout(): void
    {
        $variant = $this->stockedVariant();
        $customer = Customer::factory()->create();
        $address = Address::factory()->default()->create(['customer_id' => $customer->id, 'pincode' => '110001']);
        DeliveryZone::factory()->create(['pincodes' => ['110001'], 'delivery_charge' => 15]);

        $token = $customer->user->createToken('test')->plainTextToken;
        $auth = $this->withHeader('Authorization', "Bearer {$token}");

        $auth->postJson('/api/v1/cart/items', [
            'product_variant_id' => $variant->id,
            'quantity' => 2,
        ])->assertOk()->assertJsonFragment(['quantity' => 2]);

        $auth->getJson('/api/v1/cart')->assertOk()->assertJsonPath('data.subtotal', 160);

        $order = $auth->postJson('/api/v1/checkout', [
            'address_id' => $address->id,
            'payment_method' => 'cod',
        ])->assertCreated()->assertJsonPath('data.status', 'pending');

        $orderNumber = $order->json('data.order_number');

        $auth->getJson('/api/v1/orders')->assertOk()->assertJsonFragment(['order_number' => $orderNumber]);
        $auth->getJson("/api/v1/orders/{$orderNumber}")->assertOk();
        $auth->postJson("/api/v1/orders/{$orderNumber}/cancel")->assertOk()->assertJsonPath('data.status', 'cancelled');
    }

    public function test_unauthenticated_requests_to_protected_endpoints_are_rejected(): void
    {
        $this->getJson('/api/v1/cart')->assertUnauthorized();
        $this->getJson('/api/v1/orders')->assertUnauthorized();
    }
}
