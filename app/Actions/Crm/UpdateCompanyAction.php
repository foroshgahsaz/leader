<?php

namespace App\Actions\Crm;

use App\Contracts\Repositories\CompanyRepositoryInterface;
use App\Data\Crm\UpdateCompanyData;
use App\Enums\ActivityAction;
use App\Models\Buyer;
use App\Models\User;
use App\Services\Logging\ActivityLogger;

class UpdateCompanyAction
{
    public function __construct(
        protected CompanyRepositoryInterface $companyRepository,
        protected ActivityLogger $activityLogger,
    ) {}

    public function execute(string $companyId, UpdateCompanyData $data, User $actor): Buyer
    {
        $company = $this->companyRepository->update($companyId, $data);

        $this->activityLogger->log(
            action: ActivityAction::Updated,
            summary: "{$actor->fullName()} updated company {$company->name}",
            entityType: Buyer::class,
            entityId: $company->id,
            actor: $actor,
            buyerId: $company->id,
        );

        return $company;
    }
}
