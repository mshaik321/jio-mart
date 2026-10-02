<?php

namespace Database\Seeders;

use App\Models\Customer;
use App\Models\User;
use Illuminate\Database\Seeder;

class DemoCustomerSeeder extends Seeder
{
    public function run(): void
    {
        $user = User::query()->firstOrCreate(
            ['email' => 'customer@groceryhub.test'],
            [
                'name' => 'Demo Customer',
                'password' => 'password',
                'phone' => '9876500000',
                'email_verified_at' => now(),
                'is_active' => true,
            ],
        );

        $user->syncRoles(['Customer']);

        $customer = Customer::query()->firstOrCreate(['user_id' => $user->id]);

        if ($customer->addresses()->count() === 0) {
            $customer->addresses()->create([
                'label' => 'Home',
                'recipient_name' => $user->name,
                'phone' => $user->phone,
                'line1' => '221B Demo Street',
                'city' => 'New Delhi',
                'state' => 'Delhi',
                'pincode' => '110001',
                'country' => 'IN',
                'is_default' => true,
            ]);
        }
    }
}
