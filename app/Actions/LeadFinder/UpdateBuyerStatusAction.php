<?php

namespace App\Actions\LeadFinder;

use App\Contracts\Repositories\BuyerRepositoryInterface;
use App\Data\LeadFinder\UpdateBuyerStatusData;
use App\Enums\ActivityAction;
use App\Models\Buyer;
use App\Models\User;
use App\Services\LeadFinder\EntityHistoryService;
use App\Services\Logging\ActivityLogger;

class UpdateBuyerStatusAction
{
    public function __construct(
        protected BuyerRepositoryInterface $buyerRepository,
        protected EntityHistoryService $entityHistoryService,
        protected ActivityLogger $activityLogger,
    ) {}

    public function execute(string $buyerId, UpdateBuyerStatusData $data, User $actor): Buyer
    {
        $buyer = $this->buyerRepository->find($buyerId);

        if (! $buyer) {
            throw new \InvalidArgumentException('Buyer not found.');
        }

        $previousStatus = $buyer->status;

        $buyer = $this->buyerRepository->updateStatus($buyerId, $data);

        $this->entityHistoryService->recordChange(
            entityType: Buyer::class,
            entityId: $buyer->id,
            fieldName: 'status',
            oldValue: $previousStatus,
            newValue: $buyer->status,
            changedByUserId: $actor->id,
            reason: $data->reason,
        );

        $this->activityLogger->log(
            action: ActivityAction::Updated,
            summary: "{$actor->fullName()} changed {$buyer->name} status to {$buyer->status->label()}",
            entityType: Buyer::class,
            entityId: $buyer->id,
            actor: $actor,
            metadata: [
                'previous_status' => $previousStatus->value,
                'new_status' => $buyer->status->value,
                'reason' => $data->reason,
            ],
        );

        return $buyer;
    }
}
