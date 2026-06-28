<?php

namespace App\Enums;

enum AuditAction: string
{
    case UserLogin = 'user.login';
    case UserLogout = 'user.logout';
    case UserLoginFailed = 'user.login_failed';
    case UserRegistered = 'user.registered';
    case PasswordChanged = 'user.password_changed';
    case ProfileUpdated = 'user.profile_updated';
    case CompanyUpdated = 'organization.updated';
    case TeamMemberInvited = 'team.member_invited';
    case TeamMemberRemoved = 'team.member_removed';
    case TeamRoleChanged = 'team.role_changed';
    case SettingsUpdated = 'settings.updated';

    public function label(): string
    {
        return __('enums.audit_action.'.$this->value);
    }
}
