<?php

namespace App\Contracts\LeadFinder;

use App\Data\LeadFinder\BuyerSearchCriteriaData;

interface BuyerDataProviderInterface
{
    public function name(): string;

    public function isConfigured(): bool;

    /**
     * Fetch buyers from the external provider and persist them locally.
     *
     * @return int Number of buyers upserted
     */
    public function fetch(BuyerSearchCriteriaData $criteria): int;
}
