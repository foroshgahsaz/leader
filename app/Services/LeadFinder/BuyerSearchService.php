<?php

namespace App\Services\LeadFinder;

use App\Contracts\Repositories\BuyerSearchRepositoryInterface;
use App\Data\LeadFinder\BuyerSearchCriteriaData;
use App\Data\LeadFinder\BuyerSearchResponseData;
use App\Data\LeadFinder\BuyerSearchResultData;
use App\Enums\ScoreBand;
use App\Models\GlobalBuyer;
use App\Models\Product;
use App\Models\SearchExecution;
use App\Models\User;
use App\Support\OrganizationContext;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Pagination\LengthAwarePaginator as Paginator;
use Illuminate\Support\Collection;

class BuyerSearchService
{
    public function __construct(
        protected BuyerSearchRepositoryInterface $buyerSearchRepository,
        protected LeadScoringService $leadScoringService,
        protected OrganizationContext $organizationContext,
        protected ExternalBuyerFetchService $externalBuyerFetchService,
    ) {}

    public function search(
        BuyerSearchCriteriaData $criteria,
        User $user,
        ?string $savedSearchId = null,
        bool $fetchExternal = true,
    ): BuyerSearchResponseData {
        $startedAt = hrtime(true);

        $externalImported = $fetchExternal
            ? $this->externalBuyerFetchService->fetchForSearch($criteria)
            : null;
        $externalProvider = $externalImported !== null ? 'apollo' : null;

        $rawPaginator = $this->buyerSearchRepository->search($criteria);
        $product = $this->resolveProduct($criteria);

        $results = $this->mapResults(collect($rawPaginator->items()), $product, $criteria);

        $durationMs = (int) ((hrtime(true) - $startedAt) / 1_000_000);

        $this->logExecution(
            $criteria,
            $user,
            $savedSearchId,
            $rawPaginator->total(),
            $durationMs,
            $externalImported,
        );

        $paginator = new Paginator(
            $results->values()->all(),
            $rawPaginator->total(),
            $rawPaginator->perPage(),
            $rawPaginator->currentPage(),
            [
                'path' => $rawPaginator->path(),
                'pageName' => $rawPaginator->getPageName(),
            ],
        );

        return new BuyerSearchResponseData(
            results: $paginator,
            externalImported: $externalImported,
            externalProvider: $externalProvider,
        );
    }

    /**
     * @param  Collection<int, GlobalBuyer>  $items
     * @return Collection<int, BuyerSearchResultData>
     */
    protected function mapResults(Collection $items, ?Product $product, BuyerSearchCriteriaData $criteria): Collection
    {
        return $items
            ->map(function (GlobalBuyer $globalBuyer) use ($product): BuyerSearchResultData {
                $scoreResult = $this->leadScoringService->score($globalBuyer, $product);

                return new BuyerSearchResultData(
                    globalBuyerId: $globalBuyer->id,
                    buyerId: $globalBuyer->getAttribute('saved_buyer_id'),
                    name: $globalBuyer->display_name,
                    countryCode: $globalBuyer->country_code,
                    city: $globalBuyer->city,
                    industry: $globalBuyer->industry,
                    companyType: $globalBuyer->company_type?->value,
                    score: $scoreResult['score'],
                    scoreBand: $scoreResult['score_band'],
                    importActivityLevel: $globalBuyer->import_activity_level,
                    isSaved: $globalBuyer->getAttribute('saved_buyer_id') !== null,
                    isFavorite: (bool) $globalBuyer->getAttribute('saved_is_favorite'),
                    website: $globalBuyer->website,
                );
            })
            ->when(
                $criteria->minScore !== null,
                fn (Collection $collection) => $collection->filter(
                    fn (BuyerSearchResultData $result) => $result->score >= $criteria->minScore
                ),
            )
            ->when(
                $criteria->maxScore !== null,
                fn (Collection $collection) => $collection->filter(
                    fn (BuyerSearchResultData $result) => $result->score <= $criteria->maxScore
                ),
            );
    }

    protected function resolveProduct(BuyerSearchCriteriaData $criteria): ?Product
    {
        if (! $criteria->productId) {
            return null;
        }

        return Product::query()->find($criteria->productId);
    }

    protected function logExecution(
        BuyerSearchCriteriaData $criteria,
        User $user,
        ?string $savedSearchId,
        int $resultCount,
        int $durationMs,
        ?int $externalImported = null,
    ): void {
        $organizationId = $this->organizationContext->id();

        if (! $organizationId) {
            return;
        }

        $criteriaPayload = $criteria->toArray();

        if ($externalImported !== null) {
            $criteriaPayload['external_imported'] = $externalImported;
            $criteriaPayload['external_provider'] = config('apollo.enabled') ? 'apollo' : null;
        }

        SearchExecution::query()->create([
            'organization_id' => $organizationId,
            'user_id' => $user->id,
            'saved_search_id' => $savedSearchId,
            'criteria' => $criteriaPayload,
            'result_count' => $resultCount,
            'duration_ms' => $durationMs,
            'credit_consumed' => $externalImported !== null && $externalImported > 0 ? 1 : 0,
            'executed_at' => now(),
        ]);
    }
}
