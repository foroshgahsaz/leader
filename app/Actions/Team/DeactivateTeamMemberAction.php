<?php

namespace App\Actions\Team;

use App\Enums\ActivityAction;
use App\Enums\AuditAction;
use App\Enums\OrgMemberStatus;
use App\Models\OrgMember;
use App\Models\User;
use App\Services\Logging\ActivityLogger;
use App\Services\Logging\AuditLogger;
use Illuminate\Support\Facades\DB;

class DeactivateTeamMemberAction
{
    public function __construct(
        protected ActivityLogger $activityLogger,
        protected AuditLogger $auditLogger,
    ) {}

    public function execute(OrgMember $membership, User $actor): OrgMember
    {
        return DB::transaction(function () use ($membership, $actor): OrgMember {
            $membership->update(['status' => OrgMemberStatus::Deactivated]);
            $membership->user->roles()->detach();

            $this->activityLogger->log(
                action: ActivityAction::Deactivated,
                summary: "{$actor->fullName()} deactivated {$membership->user->email}",
                entityType: OrgMember::class,
                entityId: $membership->id,
                actor: $actor,
            );

            $this->auditLogger->log(
                action: AuditAction::TeamMemberRemoved,
                actor: $actor,
                organizationId: $membership->organization_id,
                resourceType: OrgMember::class,
                resourceId: $membership->id,
                metadata: ['user_id' => $membership->user_id],
            );

            return $membership->fresh(['user']);
        });
    }
}
