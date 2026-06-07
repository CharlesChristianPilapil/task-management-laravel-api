<?php

namespace App\Enums;

enum UserRole: string
{
    case Admin = 'admin';
    case Manager = 'manager';
    case TeamMember = 'team_member';

    public function label(): string
    {
        return match ($this) {
            self::Admin => 'Admin',
            self::Manager => 'Manager',
            self::TeamMember => 'Team Member',
        };
    }

    public static function values(): array
    {
        return array_column(self::cases(), 'value');
    }
}
