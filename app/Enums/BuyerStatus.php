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
        return __('enums.buyer_status.'.$this->value);
    }

    public static function options(): array
    {
        return collect(self::cases())
            ->mapWithKeys(fn (self $case) => [$case->value => $case->label()])
            ->all();
    }
}
