<?php

namespace App\Actions\LeadFinder;

use App\Data\LeadFinder\BuyerSearchCriteriaData;
use App\Data\LeadFinder\BuyerSearchResultData;
use App\Models\User;
use App\Services\LeadFinder\BuyerSearchService;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

class RunBuyerSearchAction
{
    public function __construct(
        protected BuyerSearchService $buyerSearchService,
    ) {}

    /**
     * @return LengthAwarePaginator<int, BuyerSearchResultData>
     */
    public function execute(
        BuyerSearchCriteriaData $criteria,
        User $user,
        ?string $savedSearchId = null,
    ): LengthAwarePaginator {
        return $this->buyerSearchService->search($criteria, $user, $savedSearchId);
    }
}
