<?php

namespace App\Actions\LeadFinder;

use App\Data\LeadFinder\BuyerSearchCriteriaData;
use App\Data\LeadFinder\BuyerSearchResponseData;
use App\Models\User;
use App\Services\LeadFinder\BuyerSearchService;

class RunBuyerSearchAction
{
    public function __construct(
        protected BuyerSearchService $buyerSearchService,
    ) {}

    public function execute(
        BuyerSearchCriteriaData $criteria,
        User $user,
        ?string $savedSearchId = null,
        bool $fetchExternal = true,
    ): BuyerSearchResponseData {
        return $this->buyerSearchService->search($criteria, $user, $savedSearchId, $fetchExternal);
    }
}
