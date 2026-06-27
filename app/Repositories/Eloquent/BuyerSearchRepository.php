<?php

namespace App\Repositories\Eloquent;

use App\Contracts\Repositories\BuyerSearchRepositoryInterface;
use App\Data\LeadFinder\BuyerSearchCriteriaData;
use App\Models\GlobalBuyer;
use App\Models\Product;
use App\Support\OrganizationContext;
use Illuminate\Contracts\Pagination\CursorPaginator;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\DB;

class BuyerSearchRepository implements BuyerSearchRepositoryInterface
{
    public function __construct(
        protected OrganizationContext $organizationContext,
    ) {}

    public function search(BuyerSearchCriteriaData $criteria): LengthAwarePaginator
    {
        return $this->buildQuery($criteria)
            ->paginate($criteria->perPage ?? 25)
            ->withQueryString();
    }

    public function cursorSearch(BuyerSearchCriteriaData $criteria): CursorPaginator
    {
        return $this->buildQuery($criteria)
            ->cursorPaginate($criteria->perPage ?? 25)
            ->withQueryString();
    }

    public function count(BuyerSearchCriteriaData $criteria): int
    {
        return $this->buildQuery($criteria)->count();
    }

    protected function buildQuery(BuyerSearchCriteriaData $criteria): Builder
    {
        $organizationId = $this->organizationContext->id();

        if (! $organizationId) {
            throw new \RuntimeException('Cannot search buyers without organization context.');
        }

        $query = GlobalBuyer::query()
            ->select([
                'global_buyers.*',
                'org_buyers.id as saved_buyer_id',
                'org_buyers.is_favorite as saved_is_favorite',
            ])
            ->leftJoin('buyers as org_buyers', function ($join) use ($organizationId): void {
                $join->on('org_buyers.global_buyer_id', '=', 'global_buyers.id')
                    ->where('org_buyers.organization_id', '=', $organizationId)
                    ->whereNull('org_buyers.deleted_at');
            });

        $this->applyFilters($query, $criteria, $organizationId);
        $this->applySort($query, $criteria);

        return $query;
    }

    protected function applyFilters(Builder $query, BuyerSearchCriteriaData $criteria, string $organizationId): void
    {
        if ($criteria->query) {
            $term = '%'.$criteria->query.'%';
            $query->where(function (Builder $builder) use ($term): void {
                $builder->where('global_buyers.display_name', 'like', $term)
                    ->orWhere('global_buyers.legal_name', 'like', $term)
                    ->orWhere('global_buyers.industry', 'like', $term);
            });
        }

        if ($criteria->countries !== []) {
            $query->whereIn('global_buyers.country_code', array_map('strtoupper', $criteria->countries));
        }

        if ($criteria->industry) {
            $query->where('global_buyers.industry', 'like', '%'.$criteria->industry.'%');
        }

        if ($criteria->companyType) {
            $query->where('global_buyers.company_type', $criteria->companyType);
        }

        if ($criteria->companyTypes !== []) {
            $query->whereIn('global_buyers.company_type', $criteria->companyTypes);
        }

        if ($criteria->product) {
            $term = '%'.$criteria->product.'%';
            $productTerm = $criteria->product;
            $query->where(function (Builder $builder) use ($term, $productTerm): void {
                $builder->where('global_buyers.industry', 'like', $term)
                    ->orWhere('global_buyers.display_name', 'like', $term)
                    ->orWhereRaw(
                        'JSON_SEARCH(global_buyers.import_profile, "one", ?, null, "$.**") IS NOT NULL',
                        [$productTerm]
                    );
            });
        }

        if ($criteria->productId) {
            $this->applyProductFilter($query, $criteria->productId);
        }

        if ($criteria->isFavorite !== null) {
            $query->whereNotNull('org_buyers.id')
                ->where('org_buyers.is_favorite', $criteria->isFavorite);
        }

        if ($criteria->status !== null) {
            $query->whereNotNull('org_buyers.id')
                ->where('org_buyers.status', $criteria->status->value);
        }

        if ($criteria->ownerId !== null) {
            $query->whereNotNull('org_buyers.id')
                ->where('org_buyers.owner_id', $criteria->ownerId);
        }

        if ($criteria->tags !== []) {
            $tags = $criteria->tags;
            $query->whereExists(function ($subQuery) use ($tags, $organizationId): void {
                $subQuery->select(DB::raw('1'))
                    ->from('buyer_tags')
                    ->join('buyers', 'buyers.id', '=', 'buyer_tags.buyer_id')
                    ->whereColumn('buyers.global_buyer_id', 'global_buyers.id')
                    ->where('buyers.organization_id', $organizationId)
                    ->whereNull('buyers.deleted_at')
                    ->whereIn('buyer_tags.tag', $tags);
            });
        }
    }

    protected function applyProductFilter(Builder $query, string $productId): void
    {
        $product = Product::query()->find($productId);

        if (! $product) {
            $query->whereRaw('1 = 0');

            return;
        }

        $hsCodes = $product->hsCodes()->pluck('hs_code')->all();

        $query->where(function (Builder $builder) use ($product, $hsCodes): void {
            $applied = false;

            if ($product->industry) {
                $builder->where('global_buyers.industry', 'like', '%'.$product->industry.'%');
                $applied = true;
            }

            if ($product->category) {
                $method = $applied ? 'orWhere' : 'where';
                $builder->{$method}('global_buyers.industry', 'like', '%'.$product->category.'%');
                $applied = true;
            }

            foreach ($hsCodes as $hsCode) {
                $method = $applied ? 'orWhereRaw' : 'whereRaw';
                $builder->{$method}(
                    'JSON_SEARCH(global_buyers.import_profile, "one", ?, null, "$.**") IS NOT NULL',
                    [$hsCode]
                );
                $applied = true;
            }

            if (! $applied) {
                $builder->whereRaw('1 = 0');
            }
        });
    }

    protected function applySort(Builder $query, BuyerSearchCriteriaData $criteria): void
    {
        $direction = strtolower($criteria->sortDirection ?? 'asc') === 'desc' ? 'desc' : 'asc';

        match ($criteria->sortBy) {
            'country' => $query->orderBy('global_buyers.country_code', $direction)
                ->orderBy('global_buyers.display_name'),
            'import_activity_level' => $query->orderBy('global_buyers.import_activity_level', $direction)
                ->orderBy('global_buyers.display_name'),
            default => $query->orderBy('global_buyers.display_name', $direction),
        };
    }
}
