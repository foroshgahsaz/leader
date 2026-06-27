<?php

namespace App\Enums;

enum BuyerStatus: string
{
    case New = 'new';
    case Saved = 'saved';
    case Contacted = 'contacted';
    case Replied = 'replied';
    case Qualified = 'qualified';
    case Unqualified = 'unqualified';
    case DoNotContact = 'do_not_contact';

    public function label(): string
    {
        return match ($this) {
            self::New => 'New',
            self::Saved => 'Saved',
            self::Contacted => 'Contacted',
            self::Replied => 'Replied',
            self::Qualified => 'Qualified',
            self::Unqualified => 'Unqualified',
            self::DoNotContact => 'Do Not Contact',
        };
    }

    public static function options(): array
    {
        return collect(self::cases())
            ->mapWithKeys(fn (self $case) => [$case->value => $case->label()])
            ->all();
    }
}
