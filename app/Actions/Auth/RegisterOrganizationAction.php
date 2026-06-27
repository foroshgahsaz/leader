<?php

namespace App\Actions\Auth;

use App\Data\Auth\RegisterOrganizationData;
use App\Enums\AuditAction;
use App\Enums\OrgMemberStatus;
use App\Enums\OrganizationRole;
use App\Enums\OrganizationStatus;
use App\Events\Auth\OrganizationRegistered;
use App\Models\Organization;
use App\Models\OrgMember;
use App\Models\User;
use App\Models\UserPreference;
use App\Services\Logging\AuditLogger;
use App\Services\Organization\OrganizationContextService;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Spatie\Permission\Models\Role;
use Spatie\Permission\PermissionRegistrar;

class RegisterOrganizationAction
{
    public function __construct(
        protected AuditLogger $auditLogger,
        protected OrganizationContextService $organizationContextService,
    ) {}

    public function execute(RegisterOrganizationData $data): User
    {
        return DB::transaction(function () use ($data): User {
            $organization = Organization::query()->create([
                'name' => $data->companyName,
                'country_code' => strtoupper($data->countryCode),
                'website' => $data->website,
                'timezone' => config('exportos.default_timezone', 'UTC'),
                'status' => OrganizationStatus::Active,
                'onboarding_completed_at' => now(),
            ]);

            $user = User::query()->create([
                'first_name' => $data->firstName,
                'last_name' => $data->lastName,
                'name' => trim("{$data->firstName} {$data->lastName}"),
                'email' => $data->email,
                'password' => Hash::make($data->password),
                'timezone' => config('exportos.default_timezone', 'UTC'),
                'locale' => config('exportos.default_locale', 'en'),
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

            $organization->setSetting('brand_voice', [
                'tone' => 'professional',
                'sign_off' => "Best regards,\n{$user->fullName()}\n{$organization->name}",
            ]);

            app(PermissionRegistrar::class)->forgetCachedPermissions();
            setPermissionsTeamId($organization->id);

            if (\Spatie\Permission\Models\Permission::query()->count() === 0) {
                (new \Database\Seeders\RolePermissionSeeder)->run();
            }

            Role::findOrCreate(OrganizationRole::Admin->value, 'web');
            Role::findOrCreate(OrganizationRole::Manager->value, 'web');
            Role::findOrCreate(OrganizationRole::Rep->value, 'web');

            $user->assignRole(OrganizationRole::Admin->value);

            $this->organizationContextService->apply($organization);

            $this->auditLogger->log(
                action: AuditAction::UserRegistered,
                actor: $user,
                organizationId: $organization->id,
                resourceType: Organization::class,
                resourceId: $organization->id,
                metadata: ['email' => $user->email],
            );

            OrganizationRegistered::dispatch($user, $organization);

            return $user;
        });
    }
}
