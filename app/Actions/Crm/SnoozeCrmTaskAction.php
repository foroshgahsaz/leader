<?php

namespace App\Actions\Crm;

use App\Enums\ActivityAction;
use App\Enums\TaskStatus;
use App\Models\Buyer;
use App\Models\Task;
use App\Models\User;
use App\Services\Logging\ActivityLogger;
use DateTimeInterface;

class SnoozeCrmTaskAction
{
    public function __construct(
        protected ActivityLogger $activityLogger,
    ) {}

    public function execute(string $taskId, DateTimeInterface $snoozedUntil, User $actor): Task
    {
        $task = Task::query()->findOrFail($taskId);

        $task->fill([
            'status' => TaskStatus::Snoozed,
            'snoozed_until' => $snoozedUntil,
        ])->save();

        $this->activityLogger->log(
            action: ActivityAction::Updated,
            summary: "{$actor->fullName()} snoozed task {$task->title}",
            entityType: $task->buyer_id ? Buyer::class : Task::class,
            entityId: $task->buyer_id ?? $task->id,
            actor: $actor,
            buyerId: $task->buyer_id,
            dealId: $task->deal_id,
        );

        return $task->fresh();
    }
}
