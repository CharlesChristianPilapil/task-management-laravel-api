<?php

namespace Database\Seeders;

use App\Enums\TaskPriority;
use App\Enums\TaskStatus;
use App\Models\Task;
use App\Models\Team;
use App\Models\User;
use Illuminate\Database\Seeder;

class TaskSeeder extends Seeder
{
    public function run(): void
    {
        $engineering = Team::where('name', 'Engineering')->first();
        $manager = User::where('email', 'manager@test.com')->first();
        $member1 = User::where('email', 'member@test.com')->first();
        $member2 = User::where('email', 'eng.member2@mailinator.com')->first();
        $member3 = User::where('email', 'eng.member3@mailinator.com')->first();

        if (! $engineering || ! $manager || ! $member1 || ! $member2 || ! $member3) {
            return;
        }

        $tasks = [
            [
                'title' => 'Setup database',
                'description' => 'Configure and migrate the Supabase PostgreSQL database.',
                'status' => TaskStatus::InProgress,
                'priority' => TaskPriority::High,
                'assigned_to' => $member1->id,
                'due_date' => now()->addDays(3),
            ],
            [
                'title' => 'Write API docs',
                'description' => 'Document all REST endpoints for the task management API.',
                'status' => TaskStatus::Pending,
                'priority' => TaskPriority::Medium,
                'assigned_to' => $member2->id,
                'due_date' => now()->addDays(7),
            ],
            [
                'title' => 'Fix login bug',
                'description' => 'Resolve JWT token expiry issue on the login endpoint.',
                'status' => TaskStatus::Completed,
                'priority' => TaskPriority::High,
                'assigned_to' => $member1->id,
                'due_date' => now()->subDays(2),
            ],
            [
                'title' => 'Design dashboard',
                'description' => 'Create wireframes and UI mockups for the analytics dashboard.',
                'status' => TaskStatus::InProgress,
                'priority' => TaskPriority::Medium,
                'assigned_to' => $member3->id,
                'due_date' => now()->addDays(5),
            ],
        ];

        foreach ($tasks as $data) {
            Task::updateOrCreate(
                ['title' => $data['title'], 'team_id' => $engineering->id],
                [
                    'description' => $data['description'],
                    'status' => $data['status'],
                    'priority' => $data['priority'],
                    'assigned_to' => $data['assigned_to'],
                    'created_by' => $manager->id,
                    'due_date' => $data['due_date'],
                ],
            );
        }
    }
}
