<?php

namespace App\Enums;

enum AiGenerationType: string
{
    case Email = 'email';
    case Whatsapp = 'whatsapp';
    case FollowUp = 'follow_up';
    case Translate = 'translate';
    case CompanySummary = 'company_summary';
    case NextBestAction = 'next_best_action';
    case RiskAnalysis = 'risk_analysis';

    public function label(): string
    {
        return __('enums.ai_generation_type.'.$this->value);
    }

    public function requiresBuyer(): bool
    {
        return $this !== self::Translate;
    }
}
