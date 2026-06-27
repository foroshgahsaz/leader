<?php

namespace App\Services\Logging;

use App\Enums\AuditAction;
use App\Models\AuditLog;
use App\Models\User;
use Illuminate\Support\Str;

class AuditLogger
{
    public function log(
        AuditAction $action,
        ?User $actor = null,
        ?string $organizationId = null,
        ?string $resourceType = null,
        ?string $resourceId = null,
        array $metadata = [],
    ): AuditLog {
        return AuditLog::query()->create([
            'organization_id' => $organizationId,
            'actor_id' => $actor?->id ?? auth()->id(),
            'actor_email' => $actor?->email ?? auth()->user()?->email,
            'action' => $action,
            'resource_type' => $resourceType,
            'resource_id' => $resourceId,
            'ip_address' => request()->ip(),
            'user_agent' => Str::limit((string) request()->userAgent(), 500, ''),
            'request_id' => request()->header('X-Request-ID') ?? (string) Str::uuid(),
            'metadata' => $metadata ?: null,
            'occurred_at' => now(),
        ]);
    }
}
