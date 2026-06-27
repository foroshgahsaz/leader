<?php

namespace App\Contracts\Repositories;

use App\Data\LeadFinder\BuyerSearchCriteriaData;
use Illuminate\Contracts\Pagination\CursorPaginator;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

interface BuyerSearchRepositoryInterface
{
    public function search(BuyerSearchCriteriaData $criteria): LengthAwarePaginator;

    public function cursorSearch(BuyerSearchCriteriaData $criteria): CursorPaginator;

    public function count(BuyerSearchCriteriaData $criteria): int;
}
