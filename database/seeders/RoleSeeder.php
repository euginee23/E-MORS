<?php

namespace Database\Seeders;

use App\Enums\UserRole;
use App\Models\Market;
use App\Models\User;
use Illuminate\Database\Seeder;

class RoleSeeder extends Seeder
{
    public function run(): void
    {
        $market = Market::updateOrCreate(
            ['name' => 'Pagadian City Public Market'],
            ['address' => 'Rizal Avenue, Barangay Dao, Pagadian City, Zamboanga del Sur 7016'],
        );

        $users = [
            [
                'name' => 'Engr. Ricardo Mangubat',
                'email' => 'admin@emors.test',
                'password' => 'password',
                'role' => UserRole::Admin,
                'market_id' => $market->id,
                'email_verified_at' => now(),
            ],
            [
                'name' => 'Mario Dela Peña',
                'email' => 'mario@emors.test',
                'password' => 'password',
                'role' => UserRole::Collector,
                'market_id' => $market->id,
                'email_verified_at' => now(),
            ],
            [
                'name' => 'Analiza Bautista',
                'email' => 'analiza@emors.test',
                'password' => 'password',
                'role' => UserRole::Collector,
                'market_id' => $market->id,
                'email_verified_at' => now(),
            ],
            [
                'name' => 'Fernando Lucero',
                'email' => 'fernando@emors.test',
                'password' => 'password',
                'role' => UserRole::Vendor,
                'market_id' => $market->id,
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
