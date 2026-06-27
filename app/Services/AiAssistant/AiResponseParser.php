<?php

namespace App\Services\AiAssistant;

use App\Enums\AiGenerationType;

class AiResponseParser
{
    /**
     * @return array<string, mixed>
     */
    public function parse(AiGenerationType $type, string $content): array
    {
        $decoded = json_decode($content, true);

        if (! is_array($decoded)) {
            throw new \RuntimeException('AI response was not valid JSON.');
        }

        $this->validateRequiredKeys($type, $decoded);

        return $decoded;
    }

    /**
     * @param  array<string, mixed>  $output
     */
    protected function validateRequiredKeys(AiGenerationType $type, array $output): void
    {
        $required = match ($type) {
            AiGenerationType::Email => ['subject', 'body'],
            AiGenerationType::Whatsapp => ['message'],
            AiGenerationType::FollowUp => ['subject', 'body'],
            AiGenerationType::Translate => ['translated_text'],
            AiGenerationType::CompanySummary => ['summary'],
            AiGenerationType::NextBestAction => ['action', 'rationale'],
            AiGenerationType::RiskAnalysis => ['risk_level', 'overall_assessment'],
        };

        foreach ($required as $key) {
            if (! array_key_exists($key, $output)) {
                throw new \RuntimeException("AI response missing required key: {$key}");
            }
        }
    }
}
