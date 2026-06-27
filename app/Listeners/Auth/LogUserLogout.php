<?php

namespace App\Listeners\Auth;

use App\Enums\AuditAction;
use App\Models\User;
use App\Services\Logging\AuditLogger;
use Illuminate\Auth\Events\Logout;

class LogUserLogout
{
    public function __construct(
        protected AuditLogger $auditLogger,
    ) {}

    public function handle(Logout $event): void
    {
        if (! $event->user instanceof User) {
            return;
        }

        $this->auditLogger->log(
            action: AuditAction::UserLogout,
            actor: $event->user,
            organizationId: $event->user->current_organization_id,
        );
    }
}
