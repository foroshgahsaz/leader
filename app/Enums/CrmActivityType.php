<?php

namespace App\Enums;

enum CrmActivityType: string
{
    case Call = 'call';
    case Email = 'email';
    case Visit = 'visit';
    case Demo = 'demo';
    case Linkedin = 'linkedin';
    case Other = 'other';

    public function label(): string
    {
        return __('enums.crm_activity_type.'.$this->value);
    }

    public static function options(): array
    {
        return collect(self::cases())
            ->mapWithKeys(fn (self $case) => [$case->value => $case->label()])
            ->all();
    }
}
