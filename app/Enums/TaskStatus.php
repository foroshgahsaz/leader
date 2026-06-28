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
        return __('enums.task_status.'.$this->value);
    }

    public static function options(): array
    {
        return collect(self::cases())
            ->mapWithKeys(fn (self $case) => [$case->value => $case->label()])
            ->all();
    }
}
