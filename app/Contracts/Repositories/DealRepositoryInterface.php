<?php

namespace App\Contracts\Repositories;

use App\Data\Crm\ChangeDealStageData;
use App\Data\Crm\CreateDealData;
use App\Models\Deal;

interface DealRepositoryInterface
{
    public function find(string $id): ?Deal;

    public function findByBuyerId(string $buyerId): ?Deal;

    public function create(CreateDealData $data, int $createdByUserId, string $stageId): Deal;

    public function changeStage(ChangeDealStageData $data, int $changedByUserId): Deal;
}
