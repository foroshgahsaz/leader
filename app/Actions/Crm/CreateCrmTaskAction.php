<?php

namespace App\Actions\Crm;

use App\Data\Crm\CreateCrmTaskData;
use App\Enums\ActivityAction;
use App\Enums\TaskPriority;
use App\Enums\TaskStatus;
use App\Models\Buyer;
use App\Models\Task;
use App\Models\User;
use App\Services\Logging\ActivityLogger;

class CreateCrmTaskAction
{
    public function __construct(
        protected ActivityLogger $activityLogger,
    ) {}

    public function execute(CreateCrmTaskData $data, User $actor): Task
    {
        $task = Task::query()->create([
            'buyer_id' => $data->buyerId,
            'deal_id' => $data->dealId,
            'assigned_to' => $data->assignedToUserId ?? $actor->id,
            'created_by' => $actor->id,
            'title' => $data->title,
            'description' => $data->description,
            'task_type' => $data->taskType,
            'priority' => TaskPriority::from($data->priority),
            'status' => TaskStatus::Pending,
            'due_at' => $data->dueAt,
        ]);

        if ($data->buyerId) {
            Buyer::query()->whereKey($data->buyerId)->update(['last_activity_at' => now()]);
        }

        $this->activityLogger->log(
            action: ActivityAction::Created,
            summary: "{$actor->fullName()} created task {$task->title}",
            entityType: $data->buyerId ? Buyer::class : Task::class,
            entityId: $data->buyerId ?? $task->id,
            actor: $actor,
            buyerId: $data->buyerId,
            dealId: $data->dealId,
        );

        return $task;
    }
}
