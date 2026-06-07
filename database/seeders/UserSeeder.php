<?php

namespace Database\Seeders;

use App\Enums\UserRole;
use App\Models\User;
use Illuminate\Database\Seeder;

class UserSeeder extends Seeder
{
    public function run(): void
    {
        $users = [
            [
                'name' => 'Admin User',
                'email' => 'admin@test.com',
                'password' => 'password123',
                'role' => UserRole::Admin,
            ],
            [
                'name' => 'Manager User',
                'email' => 'manager@test.com',
                'password' => 'password123',
                'role' => UserRole::Manager,
            ],
            [
                'name' => 'Team Member',
                'email' => 'member@test.com',
                'password' => 'password123',
                'role' => UserRole::TeamMember,
            ],
        ];

        foreach ($users as $data) {
            User::updateOrCreate(
                ['email' => $data['email']],
                [
                    'name' => $data['name'],
                    'password' => $data['password'],
                    'role' => $data['role'],
                    'is_active' => true,
                    'email_verified_at' => now(),
                ]
            );
        }
    }
}
