<?php

namespace App\Actions\LeadFinder;

use App\Contracts\Repositories\BuyerRepositoryInterface;
use App\Data\LeadFinder\CreateBuyerTaskData;
use App\Enums\ActivityAction;
use App\Models\Buyer;
use App\Models\Task;
use App\Models\User;
use App\Services\Logging\ActivityLogger;

class CreateBuyerTaskAction
{
    public function __construct(
        protected BuyerRepositoryInterface $buyerRepository,
        protected ActivityLogger $activityLogger,
    ) {}

    public function execute(
        string $buyerId,
        CreateBuyerTaskData $data,
        User $actor,
        ?int $assignedToUserId = null,
    ): Task {
        $buyer = $this->buyerRepository->find($buyerId);

        if (! $buyer) {
            throw new \InvalidArgumentException('Buyer not found.');
        }

        $assigneeId = $assignedToUserId ?? $actor->id;

        $task = $this->buyerRepository->createTask(
            buyerId: $buyerId,
            data: $data,
            assignedToUserId: $assigneeId,
            createdByUserId: $actor->id,
        );

        $this->activityLogger->log(
            action: ActivityAction::Created,
            summary: "{$actor->fullName()} created task \"{$task->title}\" for {$buyer->name}",
            entityType: Buyer::class,
            entityId: $buyer->id,
            actor: $actor,
            metadata: [
                'task_id' => $task->id,
                'assigned_to' => $assigneeId,
                'due_at' => $task->due_at?->toIso8601String(),
            ],
        );

        return $task;
    }
}
