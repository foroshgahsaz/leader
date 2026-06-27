<?php

namespace App\Policies;

use App\Models\CrmFile;
use App\Models\User;

class CrmFilePolicy
{
    public function viewAny(User $user): bool
    {
        return $user->can('crm.files.view');
    }

    public function view(User $user, CrmFile $file): bool
    {
        return $user->can('crm.files.view')
            && $user->belongsToOrganization($file->organization);
    }

    public function upload(User $user): bool
    {
        return $user->can('crm.files.upload');
    }

    public function delete(User $user, CrmFile $file): bool
    {
        return $user->can('crm.files.delete')
            && $user->belongsToOrganization($file->organization);
    }
}
