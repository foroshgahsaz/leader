<?php

namespace App\Services\LeadFinder;

use App\Contracts\LeadFinder\BuyerDataProviderInterface;
use App\Data\LeadFinder\BuyerSearchCriteriaData;
use App\Services\LeadFinder\Providers\ApolloBuyerDataProvider;

class ExternalBuyerFetchService
{
    public function __construct(
        protected ApolloBuyerDataProvider $apolloProvider,
    ) {}

    public function fetchForSearch(BuyerSearchCriteriaData $criteria): ?int
    {
        if (! config('apollo.fetch_on_search', true)) {
            return null;
        }

        $provider = $this->activeProvider();

        if ($provider === null) {
            return null;
        }

        return $provider->fetch($criteria);
    }

    protected function activeProvider(): ?BuyerDataProviderInterface
    {
        if ($this->apolloProvider->isConfigured()) {
            return $this->apolloProvider;
        }

        return null;
    }
}
