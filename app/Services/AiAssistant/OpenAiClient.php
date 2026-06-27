<?php

namespace App\Services\AiAssistant;

use App\Contracts\AiAssistant\OpenAiClientInterface;
use App\Data\AiAssistant\OpenAiChatResultData;
use OpenAI;

class OpenAiClient implements OpenAiClientInterface
{
    public function chat(array $messages, ?string $model = null): OpenAiChatResultData
    {
        $apiKey = config('openai.api_key');

        if (! $apiKey) {
            throw new \RuntimeException('OpenAI API key is not configured. Set OPENAI_API_KEY in your environment.');
        }

        $client = OpenAI::client(
            $apiKey,
            config('openai.organization'),
        );

        $response = $client->chat()->create([
            'model' => $model ?? config('openai.model'),
            'messages' => $messages,
            'max_tokens' => config('openai.max_tokens'),
            'temperature' => config('openai.temperature'),
            'response_format' => ['type' => 'json_object'],
        ]);

        $choice = $response->choices[0] ?? null;

        if (! $choice || ! isset($choice->message->content)) {
            throw new \RuntimeException('OpenAI returned an empty response.');
        }

        return new OpenAiChatResultData(
            content: $choice->message->content,
            model: $response->model,
            promptTokens: $response->usage->promptTokens ?? 0,
            completionTokens: $response->usage->completionTokens ?? 0,
            totalTokens: $response->usage->totalTokens ?? 0,
        );
    }
}
