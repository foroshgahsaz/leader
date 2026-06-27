<?php

namespace App\Actions\AiAssistant;

use App\Data\AiAssistant\RequestAiGenerationData;
use App\Models\AiGeneration;
use App\Models\Buyer;
use App\Models\User;
use App\Services\AiAssistant\AiGenerationService;

class RequestAiGenerationAction
{
    public function __construct(
        protected AiGenerationService $aiGenerationService,
    ) {}

    public function execute(RequestAiGenerationData $data, User $user): AiGeneration
    {
        abort_unless($user->can('generate', AiGeneration::class), 403);

        if ($data->buyerId) {
            $buyer = Buyer::query()->findOrFail($data->buyerId);
            abort_unless($user->belongsToOrganization($buyer->organization), 403);
        }

        return $this->aiGenerationService->request($data, $user);
    }
}
