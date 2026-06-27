<?php

namespace App\Listeners\Auth;

use App\Enums\ActivityAction;
use App\Events\Auth\OrganizationRegistered;
use App\Models\Organization;
use App\Services\Logging\ActivityLogger;
use App\Support\OrganizationContext;

class LogOrganizationRegistration
{
    public function __construct(
        protected ActivityLogger $activityLogger,
        protected OrganizationContext $organizationContext,
    ) {}

    public function handle(OrganizationRegistered $event): void
    {
        $this->organizationContext->set($event->organization);

        $this->activityLogger->log(
            action: ActivityAction::Created,
            summary: "{$event->user->fullName()} created organization {$event->organization->name}",
            entityType: Organization::class,
            entityId: $event->organization->id,
            actor: $event->user,
        );
    }
}
