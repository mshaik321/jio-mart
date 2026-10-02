<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class AdminUserSeeder extends Seeder
{
    public function run(): void
    {
        $email = env('ADMIN_EMAIL', 'admin@groceryhub.test');
        $password = env('ADMIN_PASSWORD');
        $generated = false;

        if (! $password) {
            $password = Str::password(16);
            $generated = true;
        }

        $admin = User::query()->updateOrCreate(
            ['email' => $email],
            [
                'name' => 'GroceryHub Admin',
                'password' => $password,
                'email_verified_at' => now(),
                'is_active' => true,
            ],
        );

        $admin->syncRoles(['Super Admin']);

        if ($generated) {
            $this->command?->warn("Demo admin created: {$email} / {$password} (set ADMIN_EMAIL/ADMIN_PASSWORD in .env to control this)");
        } else {
            $this->command?->info("Admin user ready: {$email}");
        }
    }
}
