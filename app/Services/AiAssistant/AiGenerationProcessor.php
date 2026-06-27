<?php

namespace App\Services\AiAssistant;

use App\Contracts\AiAssistant\OpenAiClientInterface;
use App\Enums\AiGenerationStatus;
use App\Enums\AiGenerationType;
use App\Events\AiAssistant\AiGenerationCompleted;
use App\Events\AiAssistant\AiGenerationFailed;
use App\Models\AiGeneration;
use App\Models\Buyer;
use App\Models\User;
use Throwable;

class AiGenerationProcessor
{
    public function __construct(
        protected BuyerAiContextBuilder $contextBuilder,
        protected AiPromptBuilder $promptBuilder,
        protected AiResponseParser $responseParser,
        protected OpenAiClientInterface $openAiClient,
    ) {}

    public function process(AiGeneration $generation): AiGeneration
    {
        $generation->markProcessing();

        try {
            $user = $generation->user;
            $input = $generation->input ?? [];

            if ($input['parent_id'] ?? null) {
                $input['parent_generation'] = $this->promptBuilder->parentOutput($input['parent_id']);
            }

            $context = $this->resolveContext($generation, $user, $input);
            $messages = $this->promptBuilder->build($generation->type, $context, $input);
            $result = $this->openAiClient->chat($messages);
            $output = $this->responseParser->parse($generation->type, $result->content);

            $generation->markCompleted(
                output: $output,
                model: $result->model,
                promptTokens: $result->promptTokens,
                completionTokens: $result->completionTokens,
                totalTokens: $result->totalTokens,
            );

            event(new AiGenerationCompleted($generation->fresh()));

            return $generation;
        } catch (Throwable $exception) {
            $generation->markFailed($exception->getMessage());

            event(new AiGenerationFailed($generation->fresh(), $exception->getMessage()));

            throw $exception;
        }
    }

    /**
     * @param  array<string, mixed>  $input
     * @return array<string, mixed>
     */
    protected function resolveContext(AiGeneration $generation, User $user, array $input): array
    {
        if ($generation->type === AiGenerationType::Translate) {
            return [
                'source_text' => $input['source_text'] ?? '',
                'target_language' => $input['target_language'] ?? 'en',
            ];
        }

        $buyer = $generation->buyer ?? Buyer::query()->findOrFail($generation->buyer_id);

        return $this->contextBuilder->build($buyer, $user);
    }
}
