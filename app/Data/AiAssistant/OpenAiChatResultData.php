<?php

namespace App\Data\AiAssistant;

readonly class OpenAiChatResultData
{
    public function __construct(
        public string $content,
        public string $model,
        public int $promptTokens,
        public int $completionTokens,
        public int $totalTokens,
    ) {}
}
