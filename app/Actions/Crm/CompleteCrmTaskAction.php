<?php

namespace App\Actions\Crm;

use App\Enums\ActivityAction;
use App\Enums\TaskStatus;
use App\Models\Buyer;
use App\Models\Task;
use App\Models\User;
use App\Services\Logging\ActivityLogger;

class CompleteCrmTaskAction
{
    public function __construct(
        protected ActivityLogger $activityLogger,
    ) {}

    public function execute(string $taskId, User $actor): Task
    {
        $task = Task::query()->findOrFail($taskId);

        $task->fill([
            'status' => TaskStatus::Completed,
            'completed_at' => now(),
            'completed_by' => $actor->id,
            'snoozed_until' => null,
        ])->save();

        if ($task->buyer_id) {
            Buyer::query()->whereKey($task->buyer_id)->update(['last_activity_at' => now()]);
        }

        $this->activityLogger->log(
            action: ActivityAction::Updated,
            summary: "{$actor->fullName()} completed task {$task->title}",
            entityType: $task->buyer_id ? Buyer::class : Task::class,
            entityId: $task->buyer_id ?? $task->id,
            actor: $actor,
            buyerId: $task->buyer_id,
            dealId: $task->deal_id,
        );

        return $task->fresh();
    }
}
