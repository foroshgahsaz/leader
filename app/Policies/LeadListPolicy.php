<?php

namespace App\Policies;

use App\Models\LeadList;
use App\Models\User;

class LeadListPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->can('lists.view');
    }

    public function view(User $user, LeadList $leadList): bool
    {
        return $user->can('lists.view')
            && $user->belongsToOrganization($leadList->organization);
    }

    public function create(User $user): bool
    {
        return $user->can('lists.manage');
    }

    public function update(User $user, LeadList $leadList): bool
    {
        return $user->can('lists.manage')
            && $user->belongsToOrganization($leadList->organization);
    }

    public function delete(User $user, LeadList $leadList): bool
    {
        return $user->can('lists.manage')
            && $user->belongsToOrganization($leadList->organization);
    }
}
