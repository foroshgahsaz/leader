<?php

namespace App\Listeners\Auth;

use App\Events\Auth\OrganizationRegistered;
use App\Notifications\WelcomeNotification;

class SendWelcomeNotification
{
    public function handle(OrganizationRegistered $event): void
    {
        $event->user->notify(new WelcomeNotification);
    }
}
