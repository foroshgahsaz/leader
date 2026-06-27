<?php

namespace App\Contracts\Repositories;

use App\Data\LeadFinder\CreateBuyerNoteData;
use App\Data\LeadFinder\CreateBuyerTaskData;
use App\Data\LeadFinder\SaveBuyerData;
use App\Data\LeadFinder\UpdateBuyerStatusData;
use App\Models\Buyer;
use App\Models\BuyerNote;
use App\Models\Task;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

interface BuyerRepositoryInterface
{
    public function find(string $id): ?Buyer;

    public function findByGlobalBuyerId(string $globalBuyerId): ?Buyer;

    public function existsForGlobalBuyer(string $globalBuyerId): bool;

    public function create(SaveBuyerData $data, int $createdByUserId, ?string $sourceSearchId = null): Buyer;

    public function updateStatus(string $id, UpdateBuyerStatusData $data): Buyer;

    public function updateFavorite(string $id, bool $isFavorite): Buyer;

    public function assignOwner(string $id, ?int $ownerId): Buyer;

    public function markContacted(string $id): Buyer;

    public function delete(string $id): bool;

    public function addNote(string $buyerId, CreateBuyerNoteData $data, int $createdByUserId): BuyerNote;

    public function createTask(
        string $buyerId,
        CreateBuyerTaskData $data,
        int $assignedToUserId,
        int $createdByUserId,
    ): Task;

    public function paginateForOrganization(
        ?int $ownerId = null,
        ?string $status = null,
        int $perPage = 25,
    ): LengthAwarePaginator;
}
