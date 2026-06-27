<?php

namespace App\Actions\Crm;

use App\Data\Crm\ScheduleMeetingData;
use App\Enums\ActivityAction;
use App\Enums\CrmMeetingStatus;
use App\Models\Buyer;
use App\Models\CrmMeeting;
use App\Models\User;
use App\Services\Logging\ActivityLogger;

class ScheduleMeetingAction
{
    public function __construct(
        protected ActivityLogger $activityLogger,
    ) {}

    public function execute(ScheduleMeetingData $data, User $actor): CrmMeeting
    {
        $meeting = CrmMeeting::query()->create([
            'buyer_id' => $data->buyerId,
            'deal_id' => $data->dealId,
            'contact_id' => $data->contactId,
            'organizer_id' => $actor->id,
            'title' => $data->title,
            'agenda' => $data->agenda,
            'location' => $data->location,
            'meeting_url' => $data->meetingUrl,
            'status' => CrmMeetingStatus::Scheduled,
            'starts_at' => $data->startsAt,
            'ends_at' => $data->endsAt,
        ]);

        Buyer::query()->whereKey($data->buyerId)->update(['last_activity_at' => now()]);

        $this->activityLogger->log(
            action: ActivityAction::Created,
            summary: "{$actor->fullName()} scheduled meeting {$meeting->title}",
            entityType: Buyer::class,
            entityId: $data->buyerId,
            actor: $actor,
            buyerId: $data->buyerId,
            dealId: $data->dealId,
        );

        return $meeting;
    }
}
