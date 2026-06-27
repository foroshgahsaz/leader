<?php

namespace App\Listeners\Crm;

use App\Enums\ActivityAction;
use App\Events\Crm\DealStageChanged;
use App\Models\Buyer;
use App\Models\Deal;
use App\Services\LeadFinder\EntityHistoryService;
use App\Services\Logging\ActivityLogger;

class LogDealStageChanged
{
    public function __construct(
        protected ActivityLogger $activityLogger,
        protected EntityHistoryService $entityHistoryService,
    ) {}

    public function handle(DealStageChanged $event): void
    {
        $deal = $event->deal;
        $summary = "{$event->actor->fullName()} moved {$deal->title} from "
            .($event->fromStage?->label ?? 'New')
            .' to '.$event->toStage->label;

        $this->activityLogger->log(
            action: ActivityAction::Updated,
            summary: $summary,
            entityType: Deal::class,
            entityId: $deal->id,
            actor: $event->actor,
            metadata: [
                'from_stage' => $event->fromStage?->key,
                'to_stage' => $event->toStage->key,
                'reason' => $event->reason,
            ],
            buyerId: $deal->buyer_id,
            dealId: $deal->id,
        );

        $this->entityHistoryService->recordChange(
            entityType: Buyer::class,
            entityId: $deal->buyer_id,
            fieldName: 'pipeline_stage',
            oldValue: $event->fromStage?->key,
            newValue: $event->toStage->key,
            changedByUserId: $event->actor->id,
            reason: $event->reason,
        );
    }
}
