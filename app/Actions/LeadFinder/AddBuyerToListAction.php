<?php

namespace App\Actions\LeadFinder;

use App\Contracts\Repositories\LeadListRepositoryInterface;
use App\Enums\ActivityAction;
use App\Models\LeadList;
use App\Models\LeadListItem;
use App\Models\User;
use App\Services\Logging\ActivityLogger;

class AddBuyerToListAction
{
    public function __construct(
        protected LeadListRepositoryInterface $leadListRepository,
        protected ActivityLogger $activityLogger,
    ) {}

    public function execute(string $listId, string $buyerId, User $actor): LeadListItem
    {
        $leadList = $this->leadListRepository->find($listId);

        if (! $leadList) {
            throw new \InvalidArgumentException('Lead list not found.');
        }

        $item = $this->leadListRepository->addBuyer($listId, $buyerId, $actor->id);
        $item->load('buyer');

        $buyerName = $item->buyer?->name ?? 'buyer';

        $this->activityLogger->log(
            action: ActivityAction::Updated,
            summary: "{$actor->fullName()} added {$buyerName} to list {$leadList->name}",
            entityType: LeadList::class,
            entityId: $leadList->id,
            actor: $actor,
            metadata: [
                'buyer_id' => $buyerId,
                'lead_list_item_id' => $item->id,
            ],
        );

        return $item;
    }
}
