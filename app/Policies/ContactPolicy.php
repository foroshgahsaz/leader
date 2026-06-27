<?php

namespace App\Policies;

use App\Models\BuyerContact;
use App\Models\User;

class ContactPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->can('crm.contacts.view');
    }

    public function view(User $user, BuyerContact $contact): bool
    {
        return $user->can('crm.contacts.view')
            && $user->belongsToOrganization($contact->organization);
    }

    public function create(User $user): bool
    {
        return $user->can('crm.contacts.create');
    }

    public function update(User $user, BuyerContact $contact): bool
    {
        return $user->can('crm.contacts.update')
            && $user->belongsToOrganization($contact->organization);
    }

    public function delete(User $user, BuyerContact $contact): bool
    {
        return $user->can('crm.contacts.delete')
            && $user->belongsToOrganization($contact->organization);
    }
}
