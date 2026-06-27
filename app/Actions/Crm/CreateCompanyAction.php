<?php

namespace App\Actions\Crm;

use App\Contracts\Repositories\CompanyRepositoryInterface;
use App\Data\Crm\CreateCompanyData;
use App\Enums\ActivityAction;
use App\Models\Buyer;
use App\Models\User;
use App\Services\Logging\ActivityLogger;

class CreateCompanyAction
{
    public function __construct(
        protected CompanyRepositoryInterface $companyRepository,
        protected ActivityLogger $activityLogger,
    ) {}

    public function execute(CreateCompanyData $data, User $actor): Buyer
    {
        $company = $this->companyRepository->create($data, $actor->id);

        $this->activityLogger->log(
            action: ActivityAction::Created,
            summary: "{$actor->fullName()} created company {$company->name}",
            entityType: Buyer::class,
            entityId: $company->id,
            actor: $actor,
            buyerId: $company->id,
        );

        return $company;
    }
}
