<?php

namespace App\Policies;

use App\Models\Buyer;
use App\Models\User;

class CompanyPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->can('crm.companies.view') || $user->can('leads.view');
    }

    public function view(User $user, Buyer $buyer): bool
    {
        return ($user->can('crm.companies.view') || $user->can('leads.view'))
            && $user->belongsToOrganization($buyer->organization);
    }

    public function create(User $user): bool
    {
        return $user->can('crm.companies.create');
    }

    public function update(User $user, Buyer $buyer): bool
    {
        return ($user->can('crm.companies.update') || $user->can('leads.update'))
            && $user->belongsToOrganization($buyer->organization);
    }

    public function delete(User $user, Buyer $buyer): bool
    {
        return ($user->can('crm.companies.delete') || $user->can('leads.delete'))
            && $user->belongsToOrganization($buyer->organization);
    }

    public function search(User $user): bool
    {
        return $user->can('leads.search');
    }

    public function save(User $user): bool
    {
        return $user->can('leads.save');
    }

    public function export(User $user): bool
    {
        return $user->can('leads.export');
    }

    public function import(User $user): bool
    {
        return $user->can('leads.import');
    }

    public function assign(User $user, Buyer $buyer): bool
    {
        return $user->can('leads.assign')
            && $user->belongsToOrganization($buyer->organization);
    }
}
