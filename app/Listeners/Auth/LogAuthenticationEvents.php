<?php

namespace App\Listeners\Auth;

use App\Enums\AuditAction;
use App\Services\Logging\AuditLogger;
use Illuminate\Auth\Events\Login;
use Illuminate\Auth\Events\Logout;

class LogAuthenticationEvents
{
    public function __construct(
        protected AuditLogger $auditLogger,
    ) {}

    public function handleLogin(Login $event): void
    {
        /** @var \App\Models\User $user */
        $user = $event->user;

        $user->forceFill(['last_login_at' => now()])->save();

        $this->auditLogger->log(
            action: AuditAction::UserLogin,
            actor: $user,
            organizationId: $user->current_organization_id,
            metadata: ['guard' => $event->guard],
        );
    }

    public function handleLogout(Logout $event): void
    {
        /** @var \App\Models\User|null $user */
        $user = $event->user;

        if (! $user) {
            return;
        }

        $this->auditLogger->log(
            action: AuditAction::UserLogout,
            actor: $user,
            organizationId: $user->current_organization_id,
            metadata: ['guard' => $event->guard],
        );
    }
}
