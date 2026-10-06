<?php

namespace Database\Seeders;

use App\Models\Coupon;
use App\Models\DeliveryAgent;
use App\Models\DeliverySlot;
use App\Models\DeliveryZone;
use App\Models\Store;
use App\Models\Supplier;
use App\Models\Warehouse;
use Illuminate\Database\Seeder;

class OperationsSeeder extends Seeder
{
    public function run(): void
    {
        $store = Store::query()->firstOrCreate(
            ['code' => 'STR-0001'],
            ['name' => 'GroceryHub Central Store', 'phone' => '9000000000', 'address' => 'Central Warehouse Hub', 'is_active' => true],
        );

        Warehouse::query()->firstOrCreate(
            ['code' => 'WH-0001'],
            ['store_id' => $store->id, 'name' => 'Main Fulfilment Center', 'address' => 'Primary Fulfilment Center', 'is_active' => true],
        );

        // Demo suppliers/agents are only created on an empty table so re-seeding doesn't duplicate them.
        if (Supplier::query()->doesntExist()) {
            Supplier::factory()->count(5)->create();
        }

        DeliveryZone::query()->firstOrCreate(
            ['name' => 'City Zone A'],
            ['pincodes' => ['110001', '110002', '110003'], 'delivery_charge' => 0, 'free_delivery_above' => 299, 'is_active' => true],
        );

        DeliveryZone::query()->firstOrCreate(
            ['name' => 'City Zone B'],
            ['pincodes' => ['110011', '110012', '110013'], 'delivery_charge' => 25, 'free_delivery_above' => 499, 'is_active' => true],
        );

        foreach ([['9 AM - 12 PM', '09:00', '12:00'], ['12 PM - 3 PM', '12:00', '15:00'], ['6 PM - 9 PM', '18:00', '21:00']] as [$name, $start, $end]) {
            DeliverySlot::query()->firstOrCreate(
                ['name' => $name],
                ['start_time' => $start, 'end_time' => $end, 'max_orders' => 100, 'is_active' => true],
            );
        }

        if (DeliveryAgent::query()->doesntExist()) {
            DeliveryAgent::factory()->count(3)->create();
        }

        Coupon::query()->firstOrCreate(
            ['code' => 'WELCOME10'],
            [
                'type' => 'percentage', 'value' => 10, 'min_order_amount' => 199,
                'max_discount_amount' => 100, 'starts_at' => now()->subDay(), 'ends_at' => now()->addYear(),
                'usage_limit' => null, 'per_customer_limit' => 1, 'is_active' => true,
            ],
        );

        Coupon::query()->firstOrCreate(
            ['code' => 'FLAT50'],
            [
                'type' => 'fixed', 'value' => 50, 'min_order_amount' => 499,
                'max_discount_amount' => null, 'starts_at' => now()->subDay(), 'ends_at' => now()->addYear(),
                'usage_limit' => null, 'per_customer_limit' => 2, 'is_active' => true,
            ],
        );
    }
}
