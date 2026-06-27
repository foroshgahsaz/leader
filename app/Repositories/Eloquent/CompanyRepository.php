<?php

namespace App\Repositories\Eloquent;

use App\Contracts\Repositories\CompanyRepositoryInterface;
use App\Data\Crm\CreateCompanyData;
use App\Data\Crm\CrmCompanySearchCriteriaData;
use App\Data\Crm\UpdateCompanyData;
use App\Enums\BuyerStatus;
use App\Models\Buyer;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Builder;

class CompanyRepository implements CompanyRepositoryInterface
{
    public function find(string $id): ?Buyer
    {
        return Buyer::query()->find($id);
    }

    public function create(CreateCompanyData $data, int $createdByUserId): Buyer
    {
        return Buyer::query()->create([
            'name' => $data->name,
            'country_code' => strtoupper($data->countryCode),
            'city' => $data->city,
            'website' => $data->website,
            'phone' => $data->phone,
            'industry' => $data->industry,
            'description' => $data->description,
            'company_type' => $data->companyType,
            'owner_id' => $data->ownerId ?? $createdByUserId,
            'source' => $data->source,
            'status' => BuyerStatus::Saved,
            'pipeline_stage' => 'new',
            'last_activity_at' => now(),
            'created_by' => $createdByUserId,
        ]);
    }

    public function update(string $id, UpdateCompanyData $data): Buyer
    {
        $buyer = Buyer::query()->findOrFail($id);

        $buyer->fill([
            'name' => $data->name,
            'country_code' => strtoupper($data->countryCode),
            'city' => $data->city,
            'website' => $data->website,
            'phone' => $data->phone,
            'industry' => $data->industry,
            'description' => $data->description,
            'company_type' => $data->companyType,
            'owner_id' => $data->ownerId,
            'last_activity_at' => now(),
        ])->save();

        return $buyer->fresh();
    }

    public function search(CrmCompanySearchCriteriaData $criteria): LengthAwarePaginator
    {
        $query = Buyer::query()
            ->with(['owner', 'deal.stage', 'contacts' => fn ($q) => $q->where('is_primary', true)->limit(1)]);

        $this->applyFilters($query, $criteria);

        $sortBy = $criteria->sortBy ?? 'last_activity_at';
        $direction = $criteria->sortDirection ?? 'desc';

        return $query
            ->orderBy($sortBy, $direction)
            ->orderBy('name')
            ->paginate($criteria->perPage);
    }

    public function touchActivity(string $buyerId): void
    {
        Buyer::query()->whereKey($buyerId)->update(['last_activity_at' => now()]);
    }

    protected function applyFilters(Builder $query, CrmCompanySearchCriteriaData $criteria): void
    {
        if ($criteria->query) {
            $term = '%'.$criteria->query.'%';
            $query->where(function (Builder $builder) use ($term): void {
                $builder->where('name', 'like', $term)
                    ->orWhere('industry', 'like', $term)
                    ->orWhere('city', 'like', $term)
                    ->orWhere('website', 'like', $term);
            });
        }

        if ($criteria->ownerId !== null) {
            $query->where('owner_id', $criteria->ownerId);
        }

        if ($criteria->pipelineStage) {
            $query->where('pipeline_stage', $criteria->pipelineStage);
        }

        if ($criteria->countryCode) {
            $query->where('country_code', strtoupper($criteria->countryCode));
        }

        if ($criteria->status) {
            $query->where('status', $criteria->status);
        }

        if ($criteria->industry) {
            $query->where('industry', 'like', '%'.$criteria->industry.'%');
        }

        if ($criteria->hasDeal === true) {
            $query->whereHas('deal');
        } elseif ($criteria->hasDeal === false) {
            $query->whereDoesntHave('deal');
        }
    }
}
