<?php

namespace App\Listeners\Team;

use App\Enums\ActivityAction;
use App\Events\Team\TeamMemberInvited;
use App\Models\OrgMember;
use App\Services\Logging\ActivityLogger;

class LogTeamMemberInvitation
{
    public function __construct(
        protected ActivityLogger $activityLogger,
    ) {}

    public function handle(TeamMemberInvited $event): void
    {
        $member = $event->membership->user;

        $this->activityLogger->log(
            action: ActivityAction::Invited,
            summary: "{$event->inviter->fullName()} invited {$member->email} to the team",
            entityType: OrgMember::class,
            entityId: $event->membership->id,
            actor: $event->inviter,
            metadata: [
                'email' => $member->email,
            ],
        );
    }
}
