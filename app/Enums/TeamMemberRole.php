<?php

namespace App\Enums;

enum TeamMemberRole: string
{
    case Member = 'member';
    case Lead = 'lead';

    public function label(): string
    {
        return match ($this) {
            self::Member => 'Member',
            self::Lead => 'Lead',
        };
    }

    public static function values(): array
    {
        return array_column(self::cases(), 'value');
    }
}
