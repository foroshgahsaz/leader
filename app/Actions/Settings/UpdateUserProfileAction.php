<?php

namespace App\Actions\Settings;

use App\Data\Settings\UpdateUserProfileData;
use App\Enums\AuditAction;
use App\Models\User;
use App\Services\Logging\AuditLogger;

class UpdateUserProfileAction
{
    public function __construct(
        protected AuditLogger $auditLogger,
    ) {}

    public function execute(User $user, UpdateUserProfileData $data): User
    {
        $user->fill([
            'first_name' => $data->firstName,
            'last_name' => $data->lastName,
            'job_title' => $data->jobTitle,
            'email' => $data->email,
            'timezone' => $data->timezone,
            'locale' => $data->locale,
        ]);

        $user->syncProfileName();
        $user->save();

        $this->auditLogger->log(
            action: AuditAction::ProfileUpdated,
            actor: $user,
            organizationId: $user->current_organization_id,
            resourceType: User::class,
            resourceId: (string) $user->id,
        );

        return $user->fresh();
    }
}
