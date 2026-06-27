<?php

namespace App\Actions\LeadFinder;

use App\Contracts\Repositories\SavedSearchRepositoryInterface;
use App\Data\LeadFinder\BuyerSearchCriteriaData;
use App\Enums\ActivityAction;
use App\Models\SavedSearch;
use App\Models\User;
use App\Services\Logging\ActivityLogger;

class SaveSearchAction
{
    public function __construct(
        protected SavedSearchRepositoryInterface $savedSearchRepository,
        protected ActivityLogger $activityLogger,
    ) {}

    public function execute(
        ?string $name,
        BuyerSearchCriteriaData $criteria,
        User $actor,
        ?string $savedSearchId = null,
    ): SavedSearch {
        $savedSearch = $savedSearchId
            ? $this->savedSearchRepository->update($savedSearchId, $name, $criteria)
            : $this->savedSearchRepository->create($name, $criteria, $actor->id);

        $this->activityLogger->log(
            action: $savedSearchId ? ActivityAction::Updated : ActivityAction::Created,
            summary: $savedSearchId
                ? "{$actor->fullName()} updated saved search"
                : "{$actor->fullName()} saved a search",
            entityType: SavedSearch::class,
            entityId: $savedSearch->id,
            actor: $actor,
            metadata: [
                'name' => $savedSearch->name,
                'criteria' => $criteria->toArray(),
            ],
        );

        return $savedSearch;
    }
}
