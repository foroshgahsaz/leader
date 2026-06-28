<?php

namespace App\Enums;

enum OrganizationRole: string
{
    case Admin = 'admin';
    case Manager = 'manager';
    case Rep = 'rep';

    public function label(): string
    {
        return __('enums.organization_role.'.$this->value);
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
