<?php

namespace App\Actions\Team;

use App\Data\Team\InviteTeamMemberData;
use App\Enums\AuditAction;
use App\Enums\OrgMemberStatus;
use App\Enums\OrganizationRole;
use App\Events\Team\TeamMemberInvited;
use App\Models\OrgMember;
use App\Models\Organization;
use App\Models\User;
use App\Models\UserPreference;
use App\Notifications\TeamMemberInvitedNotification;
use App\Services\Logging\AuditLogger;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Spatie\Permission\Models\Role;
use Spatie\Permission\PermissionRegistrar;

class InviteTeamMemberAction
{
    public function __construct(
        protected AuditLogger $auditLogger,
    ) {}

    public function execute(InviteTeamMemberData $data): OrgMember
    {
        return DB::transaction(function () use ($data): OrgMember {
            $organization = Organization::query()->findOrFail($data->organizationId);
            $inviter = User::query()->findOrFail($data->invitedByUserId);

            $user = User::query()->firstOrCreate(
                ['email' => $data->email],
                [
                    'name' => Str::before($data->email, '@'),
                    'first_name' => Str::before($data->email, '@'),
                    'last_name' => '',
                    'password' => Hash::make(Str::password(32)),
                ]
            );

            $membership = OrgMember::query()->updateOrCreate(
                [
                    'organization_id' => $organization->id,
                    'user_id' => $user->id,
                ],
                [
                    'status' => OrgMemberStatus::Invited,
                    'invited_at' => now(),
                    'invited_by' => $inviter->id,
                ]
            );

            if (! $user->current_organization_id) {
                $user->forceFill(['current_organization_id' => $organization->id])->save();
            }

            UserPreference::query()->firstOrCreate(
                [
                    'user_id' => $user->id,
                    'organization_id' => $organization->id,
                ],
                [
                    'preferences' => UserPreference::defaults(),
                ]
            );

            app(PermissionRegistrar::class)->forgetCachedPermissions();
            setPermissionsTeamId($organization->id);

            Role::findOrCreate($data->role->value, 'web');
            $user->syncRoles([$data->role->value]);

            $this->auditLogger->log(
                action: AuditAction::TeamMemberInvited,
                actor: $inviter,
                organizationId: $organization->id,
                resourceType: OrgMember::class,
                resourceId: $membership->id,
                metadata: [
                    'email' => $data->email,
                    'role' => $data->role->value,
                ],
            );

            $user->notify(new TeamMemberInvitedNotification($organization, $inviter, $data->role));

            TeamMemberInvited::dispatch($membership, $inviter);

            return $membership->fresh(['user', 'organization']);
        });
    }
}
