<?php

namespace App\Policies;

use App\Models\Deal;
use App\Models\User;

class DealPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->can('crm.deals.view');
    }

    public function view(User $user, Deal $deal): bool
    {
        return $user->can('crm.deals.view')
            && $user->belongsToOrganization($deal->organization);
    }

    public function create(User $user): bool
    {
        return $user->can('crm.deals.create');
    }

    public function update(User $user, Deal $deal): bool
    {
        return $user->can('crm.deals.update')
            && $user->belongsToOrganization($deal->organization);
    }

    public function changeStage(User $user, Deal $deal): bool
    {
        return $user->can('crm.deals.change-stage')
            && $user->belongsToOrganization($deal->organization);
    }
}
