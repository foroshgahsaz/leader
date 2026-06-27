<?php

namespace App\Actions\LeadFinder;

use App\Contracts\Repositories\BuyerRepositoryInterface;
use App\Enums\ActivityAction;
use App\Models\Buyer;
use App\Models\User;
use App\Services\Logging\ActivityLogger;

class ToggleFavoriteAction
{
    public function __construct(
        protected BuyerRepositoryInterface $buyerRepository,
        protected ActivityLogger $activityLogger,
    ) {}

    public function execute(string $buyerId, User $actor): Buyer
    {
        $buyer = $this->buyerRepository->find($buyerId);

        if (! $buyer) {
            throw new \InvalidArgumentException('Buyer not found.');
        }

        $isFavorite = ! $buyer->is_favorite;
        $buyer = $this->buyerRepository->updateFavorite($buyerId, $isFavorite);

        $this->activityLogger->log(
            action: ActivityAction::Updated,
            summary: $isFavorite
                ? "{$actor->fullName()} marked {$buyer->name} as favorite"
                : "{$actor->fullName()} removed {$buyer->name} from favorites",
            entityType: Buyer::class,
            entityId: $buyer->id,
            actor: $actor,
            metadata: ['is_favorite' => $isFavorite],
        );

        return $buyer;
    }
}
