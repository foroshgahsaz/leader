<?php

namespace App\Listeners\AiAssistant;

use App\Enums\AiGenerationType;
use App\Events\AiAssistant\AiGenerationCompleted;
use App\Models\BuyerSummary;
use App\Support\OrganizationContext;

class PersistCompanySummaryFromAi
{
    public function __construct(
        protected OrganizationContext $organizationContext,
    ) {}

    public function handle(AiGenerationCompleted $event): void
    {
        $generation = $event->generation->loadMissing('buyer');

        if ($generation->type !== AiGenerationType::CompanySummary || ! $generation->buyer_id) {
            return;
        }

        $output = $generation->output ?? [];

        $this->organizationContext->setId($generation->organization_id);

        $buyer = $generation->buyer;

        BuyerSummary::query()->create([
            'buyer_id' => $buyer->id,
            'global_buyer_id' => $buyer->global_buyer_id,
            'summary' => $output['summary'] ?? '',
            'key_facts' => $output['key_facts'] ?? [],
            'suggested_angle' => $output['suggested_angle'] ?? null,
            'confidence' => $output['confidence'] ?? 'medium',
            'data_gaps' => $output['data_gaps'] ?? [],
            'model_version' => $generation->model_version ?? config('openai.model_version'),
            'generated_at' => now(),
        ]);
    }
}
