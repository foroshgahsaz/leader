<?php

namespace App\Services\Organization;

use App\Models\Organization;
use App\Models\User;
use App\Models\UserPreference;
use App\Support\OrganizationContext;
use Spatie\Permission\PermissionRegistrar;

class OrganizationContextService
{
    public function __construct(
        protected OrganizationContext $context,
    ) {}

    public function resolveForUser(User $user): ?Organization
    {
        if ($user->current_organization_id) {
            $organization = Organization::query()->find($user->current_organization_id);

            if ($organization && $user->belongsToOrganization($organization)) {
                return $organization;
            }
        }

        $membership = $user->orgMemberships()
            ->where('status', 'active')
            ->oldest('joined_at')
            ->first();

        if (! $membership) {
            return null;
        }

        $organization = $membership->organization;
        $user->forceFill(['current_organization_id' => $organization->id])->save();

        return $organization;
    }

    public function setForUser(User $user, Organization $organization): void
    {
        if (! $user->belongsToOrganization($organization)) {
            abort(403, 'You are not a member of this organization.');
        }

        $user->forceFill(['current_organization_id' => $organization->id])->save();
        $this->apply($organization);
    }

    public function apply(Organization $organization): void
    {
        $this->context->set($organization);
        setPermissionsTeamId($organization->id);
        app(PermissionRegistrar::class)->forgetCachedPermissions();
    }

    public function clear(): void
    {
        $this->context->clear();
        setPermissionsTeamId(null);
    }

    public function ensurePreferences(User $user, Organization $organization): UserPreference
    {
        return UserPreference::query()->firstOrCreate(
            [
                'user_id' => $user->id,
                'organization_id' => $organization->id,
            ],
            [
                'preferences' => UserPreference::defaults(),
            ]
        );
    }
}
