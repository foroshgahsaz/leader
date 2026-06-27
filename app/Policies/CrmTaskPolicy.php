<?php

namespace App\Policies;

use App\Models\Task;
use App\Models\User;

class CrmTaskPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->can('crm.tasks.view');
    }

    public function view(User $user, Task $task): bool
    {
        return $user->can('crm.tasks.view')
            && $user->belongsToOrganization($task->organization);
    }

    public function create(User $user): bool
    {
        return $user->can('crm.tasks.create');
    }

    public function update(User $user, Task $task): bool
    {
        return $user->can('crm.tasks.update')
            && $user->belongsToOrganization($task->organization);
    }

    public function complete(User $user, Task $task): bool
    {
        return $user->can('crm.tasks.complete')
            && $user->belongsToOrganization($task->organization);
    }
}
