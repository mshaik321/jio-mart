<?php

namespace Tests\Feature\Admin;

use App\Models\Brand;
use App\Models\Coupon;
use App\Models\DeliveryAgent;
use App\Models\DeliverySlot;
use App\Models\DeliveryZone;
use App\Models\Supplier;
use App\Models\User;
use Database\Seeders\RolesAndPermissionsSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * An unchecked "Active" checkbox is not submitted, so every admin edit form must treat its
 * absence as "inactive". Regression: the update silently left the record active.
 */
class AdminDeactivationTest extends TestCase
{
    use RefreshDatabase;

    private function admin(): User
    {
        $this->seed(RolesAndPermissionsSeeder::class);
        $user = User::factory()->create();
        $user->assignRole('Super Admin');

        return $user;
    }

    public function test_unchecking_active_deactivates_a_brand(): void
    {
        $brand = Brand::factory()->create(['is_active' => true]);

        $this->actingAs($this->admin())->put("/admin/brands/{$brand->id}", ['name' => $brand->name])
            ->assertRedirect();

        $this->assertFalse($brand->fresh()->is_active);
    }

    public function test_unchecking_active_deactivates_a_supplier(): void
    {
        $supplier = Supplier::factory()->create(['is_active' => true]);

        $this->actingAs($this->admin())->put("/admin/suppliers/{$supplier->id}", ['name' => $supplier->name])
            ->assertRedirect();

        $this->assertFalse($supplier->fresh()->is_active);
    }

    public function test_unchecking_active_deactivates_a_delivery_agent(): void
    {
        $agent = DeliveryAgent::factory()->create(['is_active' => true]);

        $this->actingAs($this->admin())->put("/admin/delivery-agents/{$agent->id}", ['name' => $agent->name, 'phone' => '9000000001'])
            ->assertRedirect();

        $this->assertFalse($agent->fresh()->is_active);
    }

    public function test_unchecking_active_deactivates_a_delivery_slot(): void
    {
        $slot = DeliverySlot::factory()->create(['is_active' => true]);

        $this->actingAs($this->admin())->put("/admin/delivery-slots/{$slot->id}", [
            'name' => $slot->name, 'start_time' => '09:00', 'end_time' => '12:00', 'max_orders' => 10,
        ])->assertRedirect();

        $this->assertFalse($slot->fresh()->is_active);
    }

    public function test_unchecking_active_deactivates_a_delivery_zone(): void
    {
        $zone = DeliveryZone::factory()->create(['is_active' => true]);

        $this->actingAs($this->admin())->put("/admin/delivery-zones/{$zone->id}", [
            'name' => $zone->name, 'pincodes' => '110001', 'delivery_charge' => 0,
        ])->assertRedirect();

        $this->assertFalse($zone->fresh()->is_active);
    }

    public function test_unchecking_active_deactivates_a_coupon(): void
    {
        $coupon = Coupon::factory()->create(['is_active' => true]);

        $this->actingAs($this->admin())->put("/admin/coupons/{$coupon->id}", [
            'code' => $coupon->code, 'type' => $coupon->type->value, 'value' => $coupon->value,
        ])->assertRedirect();

        $this->assertFalse($coupon->fresh()->is_active);
    }

    public function test_unchecking_active_deactivates_a_user(): void
    {
        $admin = $this->admin();
        $user = User::factory()->create(['is_active' => true, 'password' => 'Password123!']);
        $user->assignRole('Order Manager');

        $this->actingAs($admin)->put("/admin/users/{$user->id}", ['roles' => ['Order Manager']])
            ->assertRedirect('/admin/users');

        $this->assertFalse($user->fresh()->is_active);
    }

    public function test_checking_active_keeps_a_record_active(): void
    {
        $coupon = Coupon::factory()->create(['is_active' => false]);

        $this->actingAs($this->admin())->put("/admin/coupons/{$coupon->id}", [
            'code' => $coupon->code, 'type' => $coupon->type->value, 'value' => $coupon->value, 'is_active' => '1',
        ])->assertRedirect();

        $this->assertTrue($coupon->fresh()->is_active);
    }
}
