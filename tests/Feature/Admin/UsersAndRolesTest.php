<?php

namespace Tests\Feature\Admin;

use App\Models\User;
use Database\Seeders\RolesAndPermissionsSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class UsersAndRolesTest extends TestCase
{
    use RefreshDatabase;

    private function admin(): User
    {
        $this->seed(RolesAndPermissionsSeeder::class);
        $user = User::factory()->create();
        $user->assignRole('Admin');

        return $user;
    }

    public function test_admin_can_create_staff_user_with_role(): void
    {
        $admin = $this->admin();

        $this->actingAs($admin)->post('/admin/users', [
            'name' => 'Store Manager One',
            'email' => 'store.manager@groceryhub.test',
            'password' => 'Password123!',
            'roles' => ['Store Manager'],
        ])->assertRedirect('/admin/users');

        $created = User::where('email', 'store.manager@groceryhub.test')->first();
        $this->assertNotNull($created);
        $this->assertTrue($created->hasRole('Store Manager'));
    }

    public function test_admin_can_update_user_roles(): void
    {
        $admin = $this->admin();
        $user = User::factory()->create();
        $user->assignRole('Order Manager');

        $this->actingAs($admin)->put("/admin/users/{$user->id}", [
            'roles' => ['Delivery Manager'],
            'is_active' => true,
        ])->assertRedirect('/admin/users');

        $user->refresh();
        $this->assertTrue($user->hasRole('Delivery Manager'));
        $this->assertFalse($user->hasRole('Order Manager'));
    }

    public function test_super_admin_can_change_role_permissions(): void
    {
        $this->seed(RolesAndPermissionsSeeder::class);
        $superAdmin = User::factory()->create();
        $superAdmin->assignRole('Super Admin');

        $role = Role::where('name', 'Delivery Agent')->first();

        $this->actingAs($superAdmin)->put("/admin/roles/{$role->id}", [
            'permissions' => ['delivery.view', 'delivery.manage'],
        ])->assertRedirect('/admin/roles');

        $this->assertTrue($role->fresh()->hasPermissionTo('delivery.manage'));
    }
}
