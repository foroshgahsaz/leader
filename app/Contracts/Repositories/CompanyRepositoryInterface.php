<?php

namespace App\Contracts\Repositories;

use App\Data\Crm\CrmCompanySearchCriteriaData;
use App\Data\Crm\CreateCompanyData;
use App\Data\Crm\UpdateCompanyData;
use App\Models\Buyer;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

interface CompanyRepositoryInterface
{
    public function find(string $id): ?Buyer;

    public function create(CreateCompanyData $data, int $createdByUserId): Buyer;

    public function update(string $id, UpdateCompanyData $data): Buyer;

    public function search(CrmCompanySearchCriteriaData $criteria): LengthAwarePaginator;

    public function touchActivity(string $buyerId): void;
}
