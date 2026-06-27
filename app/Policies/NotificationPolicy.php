<?php

namespace App\Policies;

use App\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;
use Illuminate\Notifications\DatabaseNotification;

class NotificationPolicy
{
    use HandlesAuthorization;

    public function viewAny(User $user): bool
    {
        return $user->can('notifications.view');
    }

    public function update(User $user, DatabaseNotification $notification): bool
    {
        return $notification->notifiable_id === $user->id
            && $notification->notifiable_type === User::class;
    }
}
