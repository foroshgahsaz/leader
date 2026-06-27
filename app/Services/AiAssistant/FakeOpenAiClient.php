<?php

namespace App\Services\AiAssistant;

use App\Contracts\AiAssistant\OpenAiClientInterface;
use App\Data\AiAssistant\OpenAiChatResultData;
use App\Enums\AiGenerationType;

class FakeOpenAiClient implements OpenAiClientInterface
{
    public function chat(array $messages, ?string $model = null): OpenAiChatResultData
    {
        $userMessage = collect($messages)
            ->filter(fn (array $message) => ($message['role'] ?? '') === 'user')
            ->last()['content'] ?? '';

        $output = $this->buildFakeOutput($userMessage);

        return new OpenAiChatResultData(
            content: json_encode($output, JSON_THROW_ON_ERROR),
            model: $model ?? 'gpt-4o-mini',
            promptTokens: 120,
            completionTokens: 180,
            totalTokens: 300,
        );
    }

    /**
     * @return array<string, mixed>
     */
    protected function buildFakeOutput(string $userMessage): array
    {
        if (str_contains($userMessage, AiGenerationType::Whatsapp->value) || str_contains($userMessage, 'WhatsApp')) {
            return [
                'message' => 'Hello! We supply premium export-grade products and would love to explore a partnership.',
                'opening_line' => 'Hi, I hope you are doing well.',
            ];
        }

        if (str_contains($userMessage, AiGenerationType::FollowUp->value) || str_contains($userMessage, 'follow-up')) {
            return [
                'subject' => 'Following up on our previous message',
                'body' => 'I wanted to follow up and see if you had a chance to review our proposal.',
                'days_after' => 3,
                'reason' => 'No response to initial outreach.',
            ];
        }

        if (str_contains($userMessage, AiGenerationType::Translate->value) || str_contains($userMessage, 'Translate')) {
            return [
                'translated_text' => '[Translated] '.$this->extractQuotedText($userMessage),
                'detected_source_language' => 'en',
            ];
        }

        if (str_contains($userMessage, AiGenerationType::CompanySummary->value) || str_contains($userMessage, 'company summary')) {
            return [
                'summary' => 'This company appears to be an active importer with established cross-border sourcing needs.',
                'key_facts' => ['Based in target market', 'Shows import activity signals'],
                'suggested_angle' => 'Lead with reliability, compliance, and landed cost transparency.',
                'confidence' => 'medium',
                'data_gaps' => ['website'],
            ];
        }

        if (str_contains($userMessage, AiGenerationType::NextBestAction->value) || str_contains($userMessage, 'next best action')) {
            return [
                'action' => 'Send a personalized introduction email highlighting product fit.',
                'priority' => 'high',
                'rationale' => 'Lead is saved but not yet contacted.',
                'suggested_due_in_days' => 2,
            ];
        }

        if (str_contains($userMessage, AiGenerationType::RiskAnalysis->value) || str_contains($userMessage, 'risk analysis')) {
            return [
                'risk_level' => 'medium',
                'risks' => [
                    [
                        'type' => 'data_quality',
                        'description' => 'Limited contact verification available.',
                        'mitigation' => 'Validate email and company website before outreach.',
                    ],
                ],
                'overall_assessment' => 'Proceed with standard due diligence before high-commitment outreach.',
            ];
        }

        return [
            'subject' => 'Partnership opportunity for your sourcing team',
            'body' => 'Dear partner, we would like to introduce our export capabilities and discuss how we can support your procurement needs.',
            'call_to_action' => 'Would you be open to a brief call next week?',
        ];
    }

    protected function extractQuotedText(string $message): string
    {
        if (preg_match('/"([^"]+)"/', $message, $matches)) {
            return $matches[1];
        }

        return 'Sample text';
    }
}
