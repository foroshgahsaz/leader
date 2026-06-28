<?php

namespace App\Enums;

enum OrgMemberStatus: string
{
    case Active = 'active';
    case Invited = 'invited';
    case Deactivated = 'deactivated';

    public function label(): string
    {
        return __('enums.org_member_status.'.$this->value);
    }
}
