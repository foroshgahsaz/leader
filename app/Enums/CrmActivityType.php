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
        return match ($this) {
            self::Call => 'Call',
            self::Email => 'Email',
            self::Visit => 'Visit',
            self::Demo => 'Demo',
            self::Linkedin => 'LinkedIn',
            self::Other => 'Other',
        };
    }

    public static function options(): array
    {
        return collect(self::cases())
            ->mapWithKeys(fn (self $case) => [$case->value => $case->label()])
            ->all();
    }
}
