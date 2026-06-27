<?php

namespace App\Services\Logging;

use App\Enums\ActivityAction;
use App\Models\ActivityLog;
use App\Models\User;
use App\Support\OrganizationContext;
use Illuminate\Support\Str;

class ActivityLogger
{
    public function __construct(
        protected OrganizationContext $organizationContext,
    ) {}

    public function log(
        ActivityAction $action,
        string $summary,
        ?string $entityType = null,
        ?string $entityId = null,
        ?User $actor = null,
        array $metadata = [],
        ?string $buyerId = null,
        ?string $dealId = null,
    ): ActivityLog {
        $organizationId = $this->organizationContext->id();

        if (! $organizationId) {
            throw new \RuntimeException('Cannot log activity without organization context.');
        }

        return ActivityLog::query()->create([
            'organization_id' => $organizationId,
            'actor_id' => $actor?->id ?? auth()->id(),
            'entity_type' => $entityType,
            'entity_id' => $entityId,
            'buyer_id' => $buyerId,
            'deal_id' => $dealId,
            'action' => $action,
            'summary' => Str::limit($summary, 500, ''),
            'metadata' => $metadata ?: null,
            'ip_address' => request()->ip(),
            'occurred_at' => now(),
        ]);
    }
}
