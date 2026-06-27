<?php

namespace App\Policies;

use App\Models\OrgMember;
use App\Models\User;

class OrgMemberPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->can('team.view');
    }

    public function invite(User $user): bool
    {
        return $user->can('team.invite');
    }

    public function update(User $user, OrgMember $orgMember): bool
    {
        return $user->can('team.manage')
            && $user->belongsToOrganization($orgMember->organization);
    }

    public function delete(User $user, OrgMember $orgMember): bool
    {
        return $user->can('team.manage')
            && $user->belongsToOrganization($orgMember->organization)
            && $user->id !== $orgMember->user_id;
    }
}
