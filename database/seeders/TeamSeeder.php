<?php

namespace Database\Seeders;

use App\Enums\TeamMemberRole;
use App\Models\Team;
use App\Models\User;
use Illuminate\Database\Seeder;

class TeamSeeder extends Seeder
{
    public function run(): void
    {
        $admin = User::where('email', 'admin@test.com')->first();
        $manager = User::where('email', 'manager@test.com')->first();
        $member = User::where('email', 'member@test.com')->first();

        if (! $admin || ! $manager || ! $member) {
            return;
        }

        $team = Team::updateOrCreate(
            ['name' => 'Default Team'],
            ['created_by' => $admin->id],
        );

        $team->members()->sync([
            $admin->id => ['role' => TeamMemberRole::Lead->value],
            $manager->id => ['role' => TeamMemberRole::Lead->value],
            $member->id => ['role' => TeamMemberRole::Member->value],
        ]);
    }
}
