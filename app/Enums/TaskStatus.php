<?php

namespace App\Enums;

enum TaskStatus: string
{
    case Pending = 'pending';
    case InProgress = 'in_progress';
    case Completed = 'completed';
    case Cancelled = 'cancelled';

    public function label(): string
    {
        return match ($this) {
            self::Pending => 'Pending',
            self::InProgress => 'In Progress',
            self::Completed => 'Completed',
            self::Cancelled => 'Cancelled',
        };
    }

    public function canTransitionTo(self $status): bool
    {
        return match ($this) {
            self::Pending => in_array($status, [self::InProgress, self::Cancelled], true),
            self::InProgress => in_array($status, [self::Completed, self::Pending], true),
            self::Completed, self::Cancelled => false,
        };
    }

    public static function values(): array
    {
        return array_column(self::cases(), 'value');
    }
}
