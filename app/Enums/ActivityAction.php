<?php

namespace App\Enums;

enum ActivityAction: string
{
    case Created = 'created';
    case Updated = 'updated';
    case Deleted = 'deleted';
    case Login = 'login';
    case Logout = 'logout';
    case Invited = 'invited';
    case Joined = 'joined';
    case Deactivated = 'deactivated';
    case RoleChanged = 'role_changed';
    case SettingsUpdated = 'settings_updated';

    public function label(): string
    {
        return __('enums.activity_action.'.$this->value);
    }
}
