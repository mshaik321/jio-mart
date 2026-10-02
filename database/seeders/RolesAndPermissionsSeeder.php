<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

class RolesAndPermissionsSeeder extends Seeder
{
    /**
     * @var array<string, list<string>>
     */
    private array $roleMatrix = [
        'Super Admin' => ['*'],
        'Admin' => [
            'products.view', 'products.create', 'products.update', 'products.delete',
            'categories.view', 'categories.create', 'categories.update', 'categories.delete',
            'brands.view', 'brands.create', 'brands.update', 'brands.delete',
            'inventory.view', 'inventory.adjust',
            'suppliers.view', 'suppliers.create', 'suppliers.update', 'suppliers.delete',
            'purchases.view', 'purchases.create', 'purchases.update', 'purchases.delete',
            'orders.view', 'orders.create', 'orders.update', 'orders.cancel',
            'coupons.view', 'coupons.create', 'coupons.update', 'coupons.delete',
            'delivery.view', 'delivery.manage',
            'returns.view', 'returns.manage',
            'reviews.view', 'reviews.moderate',
            'users.view', 'users.manage',
            'reports.view',
            'settings.manage',
            'audit_logs.view',
        ],
        'Store Manager' => [
            'products.view', 'products.create', 'products.update', 'products.delete',
            'categories.view', 'categories.create', 'categories.update', 'categories.delete',
            'brands.view', 'brands.create', 'brands.update', 'brands.delete',
            'inventory.view',
            'suppliers.view', 'suppliers.create', 'suppliers.update',
            'purchases.view', 'purchases.create', 'purchases.update',
            'reports.view',
        ],
        'Inventory Manager' => [
            'products.view',
            'inventory.view', 'inventory.adjust',
            'suppliers.view', 'suppliers.create', 'suppliers.update',
            'purchases.view', 'purchases.create', 'purchases.update', 'purchases.delete',
        ],
        'Order Manager' => [
            'orders.view', 'orders.create', 'orders.update', 'orders.cancel',
            'coupons.view', 'coupons.create', 'coupons.update', 'coupons.delete',
            'returns.view', 'returns.manage',
            'reviews.view', 'reviews.moderate',
            'reports.view',
        ],
        'Delivery Manager' => [
            'delivery.view', 'delivery.manage',
            'orders.view',
        ],
        'Delivery Agent' => [
            'delivery.view',
        ],
        'Customer' => [],
    ];

    public function run(): void
    {
        $allPermissions = collect($this->roleMatrix)
            ->flatten()
            ->reject(fn (string $permission) => $permission === '*')
            ->unique()
            ->values();

        $allPermissions->each(fn (string $name) => Permission::findOrCreate($name, 'web'));

        foreach ($this->roleMatrix as $roleName => $permissions) {
            $role = Role::findOrCreate($roleName, 'web');

            if ($permissions === ['*']) {
                $role->syncPermissions($allPermissions);

                continue;
            }

            $role->syncPermissions($permissions);
        }
    }
}
