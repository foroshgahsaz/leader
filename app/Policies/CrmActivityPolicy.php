<?php

namespace App\Policies;

use App\Models\CrmActivity;
use App\Models\User;

class CrmActivityPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->can('crm.activities.view');
    }

    public function view(User $user, CrmActivity $activity): bool
    {
        return $user->can('crm.activities.view')
            && $user->belongsToOrganization($activity->organization);
    }

    public function create(User $user): bool
    {
        return $user->can('crm.activities.create');
    }
}
