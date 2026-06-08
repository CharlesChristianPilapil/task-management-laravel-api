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
        $member1 = User::where('email', 'member@test.com')->first();
        $engMember2 = User::where('email', 'eng.member2@mailinator.com')->first();
        $engMember3 = User::where('email', 'eng.member3@mailinator.com')->first();
        $marketingLead = User::where('email', 'marketing.lead@mailinator.com')->first();
        $marketingMember1 = User::where('email', 'marketing.member1@mailinator.com')->first();
        $marketingMember2 = User::where('email', 'marketing.member2@mailinator.com')->first();
        $salesLead = User::where('email', 'sales.lead@mailinator.com')->first();
        $salesMember1 = User::where('email', 'sales.member1@mailinator.com')->first();

        if (! $admin || ! $manager || ! $member1 || ! $engMember2 || ! $engMember3
            || ! $marketingLead || ! $marketingMember1 || ! $marketingMember2
            || ! $salesLead || ! $salesMember1) {
            return;
        }

        $engineering = Team::updateOrCreate(
            ['name' => 'Engineering'],
            ['created_by' => $admin->id],
        );

        $engineering->members()->sync([
            $manager->id => ['role' => TeamMemberRole::Lead->value],
            $member1->id => ['role' => TeamMemberRole::Member->value],
            $engMember2->id => ['role' => TeamMemberRole::Member->value],
            $engMember3->id => ['role' => TeamMemberRole::Member->value],
        ]);

        $marketing = Team::updateOrCreate(
            ['name' => 'Marketing'],
            ['created_by' => $admin->id],
        );

        $marketing->members()->sync([
            $marketingLead->id => ['role' => TeamMemberRole::Lead->value],
            $marketingMember1->id => ['role' => TeamMemberRole::Member->value],
            $marketingMember2->id => ['role' => TeamMemberRole::Member->value],
        ]);

        $sales = Team::updateOrCreate(
            ['name' => 'Sales'],
            ['created_by' => $admin->id],
        );

        $sales->members()->sync([
            $salesLead->id => ['role' => TeamMemberRole::Lead->value],
            $salesMember1->id => ['role' => TeamMemberRole::Member->value],
        ]);
    }
}
