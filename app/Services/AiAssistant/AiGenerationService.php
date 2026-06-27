<?php

namespace App\Services\AiAssistant;

use App\Data\AiAssistant\RequestAiGenerationData;
use App\Enums\AiGenerationStatus;
use App\Enums\AiGenerationType;
use App\Events\AiAssistant\AiGenerationRequested;
use App\Jobs\AiAssistant\ProcessAiGenerationJob;
use App\Models\AiGeneration;
use App\Models\Buyer;
use App\Models\User;
use App\Support\OrganizationContext;

class AiGenerationService
{
    public function __construct(
        protected OrganizationContext $organizationContext,
    ) {}

    public function request(RequestAiGenerationData $data, User $user): AiGeneration
    {
        $this->validateRequest($data);

        $generation = AiGeneration::query()->create([
            'buyer_id' => $data->buyerId,
            'user_id' => $user->id,
            'type' => $data->type,
            'status' => AiGenerationStatus::Pending,
            'input' => $data->toInputArray(),
            'model_version' => config('openai.model_version'),
            'parent_id' => $data->parentId,
        ]);

        event(new AiGenerationRequested($generation));

        ProcessAiGenerationJob::dispatch($generation->id);

        return $generation;
    }

    protected function validateRequest(RequestAiGenerationData $data): void
    {
        if ($data->type->requiresBuyer() && ! $data->buyerId) {
            throw new \InvalidArgumentException('A buyer is required for this generation type.');
        }

        if ($data->type === AiGenerationType::Translate) {
            if (! $data->sourceText || ! $data->targetLanguage) {
                throw new \InvalidArgumentException('Source text and target language are required for translation.');
            }
        }

        if ($data->type === AiGenerationType::FollowUp && ! $data->parentId) {
            throw new \InvalidArgumentException('A parent email generation is required for follow-up.');
        }

        if ($data->buyerId && ! Buyer::query()->whereKey($data->buyerId)->exists()) {
            throw new \InvalidArgumentException('Buyer not found.');
        }

        if (! $this->organizationContext->has()) {
            throw new \RuntimeException('Organization context is required.');
        }
    }
}
