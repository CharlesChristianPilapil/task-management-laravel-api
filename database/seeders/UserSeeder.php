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
            [
                'name' => 'Engineering Member 2',
                'email' => 'eng.member2@mailinator.com',
                'password' => 'password123',
                'role' => UserRole::TeamMember,
            ],
            [
                'name' => 'Engineering Member 3',
                'email' => 'eng.member3@mailinator.com',
                'password' => 'password123',
                'role' => UserRole::TeamMember,
            ],
            [
                'name' => 'Marketing Lead',
                'email' => 'marketing.lead@mailinator.com',
                'password' => 'password123',
                'role' => UserRole::TeamMember,
            ],
            [
                'name' => 'Marketing Member 1',
                'email' => 'marketing.member1@mailinator.com',
                'password' => 'password123',
                'role' => UserRole::TeamMember,
            ],
            [
                'name' => 'Marketing Member 2',
                'email' => 'marketing.member2@mailinator.com',
                'password' => 'password123',
                'role' => UserRole::TeamMember,
            ],
            [
                'name' => 'Sales Lead',
                'email' => 'sales.lead@mailinator.com',
                'password' => 'password123',
                'role' => UserRole::TeamMember,
            ],
            [
                'name' => 'Sales Member 1',
                'email' => 'sales.member1@mailinator.com',
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
