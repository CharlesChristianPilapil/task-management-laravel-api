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
        $team = Team::where('name', 'Default Team')->first();
        $manager = User::where('email', 'manager@test.com')->first();
        $member = User::where('email', 'member@test.com')->first();

        if (! $team || ! $manager || ! $member) {
            return;
        }

        Task::updateOrCreate(
            ['title' => 'Review project requirements', 'team_id' => $team->id],
            [
                'description' => 'Review and confirm Module 3 task management requirements.',
                'status' => TaskStatus::Pending,
                'priority' => TaskPriority::High,
                'assigned_to' => $member->id,
                'created_by' => $manager->id,
                'due_date' => now()->addDays(7),
            ],
        );

        Task::updateOrCreate(
            ['title' => 'Prepare team standup notes', 'team_id' => $team->id],
            [
                'description' => 'Collect updates from all team members.',
                'status' => TaskStatus::InProgress,
                'priority' => TaskPriority::Medium,
                'assigned_to' => $manager->id,
                'created_by' => $manager->id,
                'due_date' => now()->addDay(),
            ],
        );
    }
}
