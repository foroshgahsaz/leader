<?php

namespace App\Actions\Settings;

use App\Data\Settings\UpdateCompanyData;
use App\Enums\ActivityAction;
use App\Enums\AuditAction;
use App\Models\Organization;
use App\Models\User;
use App\Services\Logging\ActivityLogger;
use App\Services\Logging\AuditLogger;

class UpdateCompanyProfileAction
{
    public function __construct(
        protected ActivityLogger $activityLogger,
        protected AuditLogger $auditLogger,
    ) {}

    public function execute(Organization $organization, UpdateCompanyData $data, User $actor): Organization
    {
        $organization->fill([
            'name' => $data->name,
            'country_code' => strtoupper($data->countryCode),
            'website' => $data->website,
            'industry' => $data->industry,
            'timezone' => $data->timezone,
        ])->save();

        $this->activityLogger->log(
            action: ActivityAction::Updated,
            summary: "{$actor->fullName()} updated company profile",
            entityType: Organization::class,
            entityId: $organization->id,
            actor: $actor,
        );

        $this->auditLogger->log(
            action: AuditAction::CompanyUpdated,
            actor: $actor,
            organizationId: $organization->id,
            resourceType: Organization::class,
            resourceId: $organization->id,
            metadata: [
                'name' => $data->name,
                'country_code' => $data->countryCode,
                'website' => $data->website,
                'industry' => $data->industry,
                'timezone' => $data->timezone,
            ],
        );

        return $organization->fresh();
    }
}
