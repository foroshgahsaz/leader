<?php

namespace App\Data\LeadFinder;

use Illuminate\Contracts\Pagination\LengthAwarePaginator;

readonly class BuyerSearchResponseData
{
    /**
     * @param  LengthAwarePaginator<int, BuyerSearchResultData>  $results
     */
    public function __construct(
        public LengthAwarePaginator $results,
        public ?int $externalImported = null,
        public ?string $externalProvider = null,
    ) {}
}
