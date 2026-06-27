<?php

namespace App\Actions\Crm;

use App\Data\Crm\LogCrmActivityData;
use App\Enums\ActivityAction;
use App\Models\Buyer;
use App\Models\CrmActivity;
use App\Models\User;
use App\Services\Logging\ActivityLogger;

class LogCrmActivityAction
{
    public function __construct(
        protected ActivityLogger $activityLogger,
    ) {}

    public function execute(LogCrmActivityData $data, User $actor): CrmActivity
    {
        $activity = CrmActivity::query()->create([
            'buyer_id' => $data->buyerId,
            'deal_id' => $data->dealId,
            'contact_id' => $data->contactId,
            'logged_by' => $actor->id,
            'activity_type' => $data->activityType,
            'subject' => $data->subject,
            'body' => $data->body,
            'duration_minutes' => $data->durationMinutes,
            'occurred_at' => $data->occurredAt ?? now(),
        ]);

        Buyer::query()->whereKey($data->buyerId)->update([
            'last_contacted_at' => now(),
            'last_activity_at' => now(),
        ]);

        $this->activityLogger->log(
            action: ActivityAction::Created,
            summary: "{$actor->fullName()} logged {$data->activityType->label()}: {$data->subject}",
            entityType: Buyer::class,
            entityId: $data->buyerId,
            actor: $actor,
            buyerId: $data->buyerId,
            dealId: $data->dealId,
        );

        return $activity;
    }
}
