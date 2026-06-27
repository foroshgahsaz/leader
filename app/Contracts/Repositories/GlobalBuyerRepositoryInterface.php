<?php

namespace App\Contracts\Repositories;

use App\Data\LeadFinder\ImportLeadRowData;
use App\Models\GlobalBuyer;
use Illuminate\Support\Collection;

interface GlobalBuyerRepositoryInterface
{
    public function find(string $id): ?GlobalBuyer;

    public function findByProviderKey(string $providerKey): ?GlobalBuyer;

    public function findMany(array $ids): Collection;

    public function create(array $attributes): GlobalBuyer;

    public function update(string $id, array $attributes): GlobalBuyer;

    public function upsertFromImport(ImportLeadRowData $data, string $providerKey): GlobalBuyer;

    public function delete(string $id): bool;
}
