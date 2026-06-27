<?php

namespace App\Data\AiAssistant;

use App\Enums\AiGenerationType;

readonly class RequestAiGenerationData
{
    public function __construct(
        public AiGenerationType $type,
        public ?string $buyerId = null,
        public ?string $parentId = null,
        public ?string $tone = null,
        public ?string $language = null,
        public ?string $additionalInstructions = null,
        public ?string $sourceText = null,
        public ?string $targetLanguage = null,
    ) {}

    public function toInputArray(): array
    {
        return array_filter([
            'type' => $this->type->value,
            'buyer_id' => $this->buyerId,
            'parent_id' => $this->parentId,
            'tone' => $this->tone,
            'language' => $this->language,
            'additional_instructions' => $this->additionalInstructions,
            'source_text' => $this->sourceText,
            'target_language' => $this->targetLanguage,
        ], fn (mixed $value) => $value !== null && $value !== '');
    }
}
