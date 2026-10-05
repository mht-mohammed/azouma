<?php

namespace Database\Seeders;

use App\Enums\UserRole;
use App\Models\User;
use Illuminate\Database\Seeder;

class DemoUserSeeder extends Seeder
{
    public function run(): void
    {
        // LOCAL DEMO credentials only — never use these in production.
        // Password for every demo account: password
        $accounts = [
            ['name' => 'مدير المنصة', 'email' => 'admin@azouma.local', 'role' => UserRole::ADMIN],
            ['name' => 'صاحب مطعم ١', 'email' => 'owner1@azouma.local', 'role' => UserRole::OWNER],
            ['name' => 'صاحب مطعم ٢', 'email' => 'owner2@azouma.local', 'role' => UserRole::OWNER],
            ['name' => 'عميل تجريبي', 'email' => 'customer@azouma.local', 'role' => UserRole::CUSTOMER],
        ];

        foreach ($accounts as $account) {
            User::firstOrCreate(
                ['email' => $account['email']],
                [
                    'name' => $account['name'],
                    'password' => 'password',
                    'role' => $account['role'],
                ]
            );
        }
    }
}
