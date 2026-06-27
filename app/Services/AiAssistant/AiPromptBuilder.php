<?php

namespace App\Services\AiAssistant;

use App\Enums\AiGenerationType;
use App\Models\AiGeneration;

class AiPromptBuilder
{
    /**
     * @param  array<string, mixed>  $context
     * @param  array<string, mixed>  $input
     * @return array<int, array{role: string, content: string}>
     */
    public function build(AiGenerationType $type, array $context, array $input): array
    {
        return [
            [
                'role' => 'system',
                'content' => $this->systemPrompt($type),
            ],
            [
                'role' => 'user',
                'content' => $this->userPrompt($type, $context, $input),
            ],
        ];
    }

    protected function systemPrompt(AiGenerationType $type): string
    {
        $base = 'You are ExportOS AI Sales Assistant helping B2B exporters with international buyer outreach. '
            .'Respond ONLY with valid JSON matching the requested schema. Be professional, concise, and actionable. '
            .'Never invent specific pricing, contracts, or certifications that were not provided in context.';

        return match ($type) {
            AiGenerationType::Email => $base.' Schema: {"subject":"string","body":"string","call_to_action":"string"}',
            AiGenerationType::Whatsapp => $base.' Schema: {"message":"string","opening_line":"string"} Keep message under 500 characters.',
            AiGenerationType::FollowUp => $base.' Schema: {"subject":"string","body":"string","days_after":number,"reason":"string"}',
            AiGenerationType::Translate => $base.' Schema: {"translated_text":"string","detected_source_language":"string"}',
            AiGenerationType::CompanySummary => $base.' Schema: {"summary":"string","key_facts":["string"],"suggested_angle":"string","confidence":"high|medium|low","data_gaps":["string"]}',
            AiGenerationType::NextBestAction => $base.' Schema: {"action":"string","priority":"high|medium|low","rationale":"string","suggested_due_in_days":number}',
            AiGenerationType::RiskAnalysis => $base.' Schema: {"risk_level":"low|medium|high","risks":[{"type":"string","description":"string","mitigation":"string"}],"overall_assessment":"string"}',
        };
    }

    /**
     * @param  array<string, mixed>  $context
     * @param  array<string, mixed>  $input
     */
    protected function userPrompt(AiGenerationType $type, array $context, array $input): string
    {
        $payload = [
            'task' => $type->value,
            'context' => $context,
            'options' => array_filter([
                'tone' => $input['tone'] ?? null,
                'language' => $input['language'] ?? null,
                'additional_instructions' => $input['additional_instructions'] ?? null,
                'target_language' => $input['target_language'] ?? null,
                'source_text' => $input['source_text'] ?? null,
                'parent_generation' => $input['parent_generation'] ?? null,
            ]),
        ];

        return json_encode($payload, JSON_THROW_ON_ERROR | JSON_PRETTY_PRINT);
    }

    /**
     * @return array<string, mixed>|null
     */
    public function parentOutput(?string $parentId): ?array
    {
        if (! $parentId) {
            return null;
        }

        $parent = AiGeneration::query()->find($parentId);

        return $parent?->output;
    }
}
