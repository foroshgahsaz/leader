<?php

namespace App\Listeners\AiAssistant;

use App\Enums\ActivityAction;
use App\Events\AiAssistant\AiGenerationCompleted;
use App\Events\AiAssistant\AiGenerationFailed;
use App\Events\AiAssistant\AiGenerationRequested;
use App\Models\AiGeneration;
use App\Models\Buyer;
use App\Services\Logging\ActivityLogger;
use App\Support\OrganizationContext;

class LogAiGenerationActivity
{
    public function __construct(
        protected ActivityLogger $activityLogger,
        protected OrganizationContext $organizationContext,
    ) {}

    public function handleRequested(AiGenerationRequested $event): void
    {
        $this->withContext($event->generation, function () use ($event): void {
            $generation = $event->generation->loadMissing('user', 'buyer');

            $this->activityLogger->log(
                action: ActivityAction::Created,
                summary: "{$generation->user->fullName()} requested {$generation->type->label()} generation",
                entityType: $generation->buyer_id ? Buyer::class : AiGeneration::class,
                entityId: $generation->buyer_id ?? $generation->id,
                actor: $generation->user,
                metadata: [
                    'ai_generation_id' => $generation->id,
                    'type' => $generation->type->value,
                ],
            );
        });
    }

    public function handleCompleted(AiGenerationCompleted $event): void
    {
        $this->withContext($event->generation, function () use ($event): void {
            $generation = $event->generation->loadMissing('user', 'buyer');
            $targetName = $generation->buyer?->name ?? 'translation';

            $this->activityLogger->log(
                action: ActivityAction::Updated,
                summary: "AI {$generation->type->label()} generation completed for {$targetName}",
                entityType: $generation->buyer_id ? Buyer::class : AiGeneration::class,
                entityId: $generation->buyer_id ?? $generation->id,
                actor: $generation->user,
                metadata: [
                    'ai_generation_id' => $generation->id,
                    'type' => $generation->type->value,
                    'total_tokens' => $generation->total_tokens,
                ],
            );
        });
    }

    public function handleFailed(AiGenerationFailed $event): void
    {
        $this->withContext($event->generation, function () use ($event): void {
            $generation = $event->generation->loadMissing('user', 'buyer');

            $this->activityLogger->log(
                action: ActivityAction::Updated,
                summary: "AI {$generation->type->label()} generation failed",
                entityType: $generation->buyer_id ? Buyer::class : AiGeneration::class,
                entityId: $generation->buyer_id ?? $generation->id,
                actor: $generation->user,
                metadata: [
                    'ai_generation_id' => $generation->id,
                    'error' => $event->errorMessage,
                ],
            );
        });
    }

    protected function withContext(AiGeneration $generation, callable $callback): void
    {
        $this->organizationContext->setId($generation->organization_id);

        $callback();
    }
}
