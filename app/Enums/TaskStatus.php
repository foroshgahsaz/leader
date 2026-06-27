<?php

namespace App\Enums;

enum TaskStatus: string
{
    case Pending = 'pending';
    case Snoozed = 'snoozed';
    case Completed = 'completed';
    case Cancelled = 'cancelled';

    public function label(): string
    {
        return match ($this) {
            self::Pending => 'Pending',
            self::Snoozed => 'Snoozed',
            self::Completed => 'Completed',
            self::Cancelled => 'Cancelled',
        };
    }

    public static function options(): array
    {
        return collect(self::cases())
            ->mapWithKeys(fn (self $case) => [$case->value => $case->label()])
            ->all();
    }
}
