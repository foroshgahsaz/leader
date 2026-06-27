<?php

namespace Tests\Concerns;

use App\Enums\OrgMemberStatus;
use App\Enums\OrganizationRole;
use App\Enums\OrganizationStatus;
use App\Models\Organization;
use App\Models\OrgMember;
use App\Models\User;
use App\Models\UserPreference;
use Database\Seeders\RolePermissionSeeder;
use Spatie\Permission\Models\Role;
use Spatie\Permission\PermissionRegistrar;

trait CreatesAuthenticatedOrganizationUser
{
    protected function createOrganizationUser(
        OrganizationRole $role = OrganizationRole::Admin,
    ): User {
        (new RolePermissionSeeder)->run();

        $organization = Organization::query()->create([
            'name' => 'Test Exports Ltd',
            'country_code' => 'TR',
            'timezone' => 'UTC',
            'status' => OrganizationStatus::Active,
            'onboarding_completed_at' => now(),
        ]);

        $user = User::factory()->create([
            'first_name' => 'Test',
            'last_name' => 'User',
            'name' => 'Test User',
            'current_organization_id' => $organization->id,
        ]);

        OrgMember::query()->create([
            'organization_id' => $organization->id,
            'user_id' => $user->id,
            'status' => OrgMemberStatus::Active,
            'joined_at' => now(),
        ]);

        UserPreference::query()->create([
            'user_id' => $user->id,
            'organization_id' => $organization->id,
            'preferences' => UserPreference::defaults(),
        ]);

        app(PermissionRegistrar::class)->forgetCachedPermissions();
        setPermissionsTeamId($organization->id);

        $user->assignRole(Role::findOrCreate($role->value, 'web'));

        return $user->fresh();
    }
}
