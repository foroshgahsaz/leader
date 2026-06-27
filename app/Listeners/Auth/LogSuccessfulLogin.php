<?php

namespace App\Listeners\Auth;

use App\Enums\AuditAction;
use App\Services\Logging\AuditLogger;
use Illuminate\Auth\Events\Login;

class LogSuccessfulLogin
{
    public function __construct(
        protected AuditLogger $auditLogger,
    ) {}

    public function handle(Login $event): void
    {
        if (! $event->user instanceof \App\Models\User) {
            return;
        }

        $event->user->forceFill(['last_login_at' => now()])->save();

        $this->auditLogger->log(
            action: AuditAction::UserLogin,
            actor: $event->user,
            organizationId: $event->user->current_organization_id,
        );
    }
}
