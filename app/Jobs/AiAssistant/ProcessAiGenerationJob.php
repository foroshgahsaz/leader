<?php

namespace App\Jobs\AiAssistant;

use App\Models\AiGeneration;
use App\Services\AiAssistant\AiGenerationProcessor;
use App\Support\OrganizationContext;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Throwable;

class ProcessAiGenerationJob implements ShouldQueue
{
    use Queueable;

    public int $tries = 3;

    public int $timeout = 120;

    public function __construct(
        public string $aiGenerationId,
    ) {}

    public function handle(
        AiGenerationProcessor $processor,
        OrganizationContext $organizationContext,
    ): void {
        $generation = AiGeneration::query()->withoutGlobalScopes()->find($this->aiGenerationId);

        if (! $generation || $generation->status->isTerminal()) {
            return;
        }

        $organizationContext->setId($generation->organization_id);

        try {
            $processor->process($generation);
        } catch (Throwable) {
            // Failure event is dispatched inside the processor.
        }
    }
}
