<?php

namespace App\Actions\Crm;

use App\Enums\ActivityAction;
use App\Enums\CrmMeetingStatus;
use App\Models\Buyer;
use App\Models\CrmMeeting;
use App\Models\User;
use App\Services\Logging\ActivityLogger;

class CompleteMeetingAction
{
    public function __construct(
        protected ActivityLogger $activityLogger,
    ) {}

    public function execute(string $meetingId, ?string $outcome, User $actor): CrmMeeting
    {
        $meeting = CrmMeeting::query()->findOrFail($meetingId);

        $meeting->fill([
            'status' => CrmMeetingStatus::Completed,
            'outcome' => $outcome,
            'completed_at' => now(),
        ])->save();

        Buyer::query()->whereKey($meeting->buyer_id)->update([
            'last_contacted_at' => now(),
            'last_activity_at' => now(),
        ]);

        $this->activityLogger->log(
            action: ActivityAction::Updated,
            summary: "{$actor->fullName()} completed meeting {$meeting->title}",
            entityType: Buyer::class,
            entityId: $meeting->buyer_id,
            actor: $actor,
            buyerId: $meeting->buyer_id,
            dealId: $meeting->deal_id,
        );

        return $meeting->fresh();
    }
}
