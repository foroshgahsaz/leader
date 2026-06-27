<?php

namespace App\Enums;

enum OrgMemberStatus: string
{
    case Active = 'active';
    case Invited = 'invited';
    case Deactivated = 'deactivated';

    public function label(): string
    {
        return match ($this) {
            self::Active => 'Active',
            self::Invited => 'Invited',
            self::Deactivated => 'Deactivated',
        };
    }
}
