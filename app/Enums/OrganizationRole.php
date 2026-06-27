<?php

namespace App\Enums;

enum OrganizationRole: string
{
    case Admin = 'admin';
    case Manager = 'manager';
    case Rep = 'rep';

    public function label(): string
    {
        return match ($this) {
            self::Admin => 'Administrator',
            self::Manager => 'Manager',
            self::Rep => 'Sales Representative',
        };
    }

    public static function assignableBy(OrganizationRole $actor): array
    {
        return match ($actor) {
            self::Admin => [self::Admin, self::Manager, self::Rep],
            self::Manager => [self::Rep],
            self::Rep => [],
        };
    }
}
