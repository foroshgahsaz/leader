<?php

namespace App\Contracts\AiAssistant;

use App\Data\AiAssistant\OpenAiChatResultData;

interface OpenAiClientInterface
{
    /**
     * @param  array<int, array{role: string, content: string}>  $messages
     */
    public function chat(array $messages, ?string $model = null): OpenAiChatResultData;
}
