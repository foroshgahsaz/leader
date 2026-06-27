<?php

namespace App\Contracts\Repositories;

use App\Data\LeadFinder\BuyerSearchCriteriaData;
use App\Models\SavedSearch;
use Illuminate\Support\Collection;

interface SavedSearchRepositoryInterface
{
    public function find(string $id): ?SavedSearch;

    public function listForOrganization(?int $createdByUserId = null): Collection;

    public function create(?string $name, BuyerSearchCriteriaData $criteria, int $createdByUserId): SavedSearch;

    public function update(string $id, ?string $name, BuyerSearchCriteriaData $criteria): SavedSearch;

    public function recordLastRun(string $id, int $resultCount): SavedSearch;

    public function delete(string $id): bool;
}
