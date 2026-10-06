<?php

namespace Tests\Feature\Storefront;

use App\Models\Customer;
use App\Models\User;
use Database\Seeders\RolesAndPermissionsSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class CustomerProfileTest extends TestCase
{
    use RefreshDatabase;

    private function adminWithoutCustomerProfile(): User
    {
        $this->seed(RolesAndPermissionsSeeder::class);
        $admin = User::factory()->create(['name' => 'Ravi']);
        $admin->assignRole('Admin');

        return $admin;
    }

    public function test_staff_accounts_can_open_every_shopping_page(): void
    {
        $admin = $this->adminWithoutCustomerProfile();
        $this->assertNull($admin->customer);

        foreach (['/orders', '/addresses', '/addresses/create', '/checkout', '/wishlist', '/cart', '/dashboard', '/profile'] as $path) {
            $this->actingAs($admin)->get($path)->assertOk();
        }

        $this->assertSame(1, Customer::where('user_id', $admin->id)->count(), 'The profile is created once, on first use.');
    }

    public function test_staff_accounts_can_save_a_delivery_address(): void
    {
        $admin = $this->adminWithoutCustomerProfile();

        $this->actingAs($admin)->post('/addresses', [
            'label' => 'Home',
            'recipient_name' => 'Ravi',
            'phone' => '9876543210',
            'line1' => '1-2-3 Main Road',
            'city' => 'Hyderabad',
            'state' => 'Telangana',
            'pincode' => '500001',
        ])->assertRedirect();

        $this->assertSame(1, $admin->fresh()->customer->addresses()->count());
    }

    public function test_storefront_header_links_staff_to_the_admin_panel(): void
    {
        $admin = $this->adminWithoutCustomerProfile();

        $this->actingAs($admin)->get('/')->assertOk()->assertSee(route('admin.dashboard'));
    }

    public function test_api_shopping_endpoints_work_for_accounts_without_a_profile_yet(): void
    {
        Sanctum::actingAs($this->adminWithoutCustomerProfile());

        $this->getJson('/api/v1/orders')->assertOk();
        $this->getJson('/api/v1/addresses')->assertOk();
        $this->getJson('/api/v1/cart')->assertSuccessful();
    }
}
