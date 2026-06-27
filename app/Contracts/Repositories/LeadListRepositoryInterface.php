<?php

namespace App\Contracts\Repositories;

use App\Models\LeadList;
use App\Models\LeadListItem;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;

interface LeadListRepositoryInterface
{
    public function find(string $id): ?LeadList;

    public function listForOrganization(?int $ownerId = null): Collection;

    public function create(array $attributes, int $createdByUserId): LeadList;

    public function update(string $id, array $attributes): LeadList;

    public function delete(string $id): bool;

    public function addBuyer(string $listId, string $buyerId, int $addedByUserId): LeadListItem;

    public function removeBuyer(string $listId, string $buyerId): bool;

    public function getBuyers(string $listId, int $perPage = 25): LengthAwarePaginator;
}
