<?php

namespace Database\Seeders;

use App\Enums\UserRole;
use App\Models\User;
use Illuminate\Database\Seeder;

class RoleSeeder extends Seeder
{
    /**
     * Seed test user accounts with different roles.
     */
    public function run(): void
    {
        $users = [
            [
                'name' => 'Admin User',
                'email' => 'admin@emors.test',
                'password' => 'password',
                'role' => UserRole::Admin,
                'email_verified_at' => now(),
            ],
            [
                'name' => 'Collector User',
                'email' => 'collector@emors.test',
                'password' => 'password',
                'role' => UserRole::Collector,
                'email_verified_at' => now(),
            ],
            [
                'name' => 'Vendor User',
                'email' => 'vendor@emors.test',
                'password' => 'password',
                'role' => UserRole::Vendor,
                'email_verified_at' => now(),
            ],
        ];

        foreach ($users as $userData) {
            User::updateOrCreate(
                ['email' => $userData['email']],
                $userData,
            );
        }
    }
}
