<?php

namespace App\Actions\Team;

use App\Enums\ActivityAction;
use App\Enums\AuditAction;
use App\Enums\OrgMemberStatus;
use App\Enums\OrganizationRole;
use App\Models\OrgMember;
use App\Models\User;
use App\Services\Logging\ActivityLogger;
use App\Services\Logging\AuditLogger;
use Illuminate\Support\Facades\DB;
use Spatie\Permission\Models\Role;
use Spatie\Permission\PermissionRegistrar;

class ChangeTeamMemberRoleAction
{
    public function __construct(
        protected ActivityLogger $activityLogger,
        protected AuditLogger $auditLogger,
    ) {}

    public function execute(OrgMember $membership, OrganizationRole $role, User $actor): OrgMember
    {
        return DB::transaction(function () use ($membership, $role, $actor): OrgMember {
            app(PermissionRegistrar::class)->forgetCachedPermissions();
            setPermissionsTeamId($membership->organization_id);

            Role::findOrCreate($role->value, 'web');
            $membership->user->syncRoles([$role->value]);

            $this->activityLogger->log(
                action: ActivityAction::RoleChanged,
                summary: "{$actor->fullName()} changed {$membership->user->email} role to {$role->label()}",
                entityType: OrgMember::class,
                entityId: $membership->id,
                actor: $actor,
                metadata: ['role' => $role->value],
            );

            $this->auditLogger->log(
                action: AuditAction::TeamRoleChanged,
                actor: $actor,
                organizationId: $membership->organization_id,
                resourceType: OrgMember::class,
                resourceId: $membership->id,
                metadata: [
                    'user_id' => $membership->user_id,
                    'role' => $role->value,
                ],
            );

            return $membership->fresh(['user']);
        });
    }
}
