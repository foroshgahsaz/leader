<?php

namespace App\Repositories\Eloquent;

use App\Contracts\Repositories\SavedSearchRepositoryInterface;
use App\Data\LeadFinder\BuyerSearchCriteriaData;
use App\Models\SavedSearch;
use Illuminate\Support\Collection;

class SavedSearchRepository implements SavedSearchRepositoryInterface
{
    public function find(string $id): ?SavedSearch
    {
        return SavedSearch::query()->find($id);
    }

    public function listForOrganization(?int $createdByUserId = null): Collection
    {
        return SavedSearch::query()
            ->with('creator')
            ->when($createdByUserId !== null, fn ($query) => $query->where('created_by', $createdByUserId))
            ->orderByDesc('updated_at')
            ->get();
    }

    public function create(?string $name, BuyerSearchCriteriaData $criteria, int $createdByUserId): SavedSearch
    {
        return SavedSearch::query()->create([
            'name' => $name,
            'criteria' => $criteria->toArray(),
            'created_by' => $createdByUserId,
        ]);
    }

    public function update(string $id, ?string $name, BuyerSearchCriteriaData $criteria): SavedSearch
    {
        $savedSearch = SavedSearch::query()->findOrFail($id);

        $savedSearch->fill([
            'name' => $name ?? $savedSearch->name,
            'criteria' => $criteria->toArray(),
        ])->save();

        return $savedSearch->fresh();
    }

    public function recordLastRun(string $id, int $resultCount): SavedSearch
    {
        $savedSearch = SavedSearch::query()->findOrFail($id);

        $savedSearch->fill([
            'result_count_last' => $resultCount,
            'last_run_at' => now(),
        ])->save();

        return $savedSearch->fresh();
    }

    public function delete(string $id): bool
    {
        $savedSearch = SavedSearch::query()->find($id);

        if (! $savedSearch) {
            return false;
        }

        return (bool) $savedSearch->delete();
    }
}
