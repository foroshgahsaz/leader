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
        return match ($this) {
            self::Email => 'Email',
            self::Whatsapp => 'WhatsApp',
            self::FollowUp => 'Follow-up',
            self::Translate => 'Translate',
            self::CompanySummary => 'Company Summary',
            self::NextBestAction => 'Next Best Action',
            self::RiskAnalysis => 'Risk Analysis',
        };
    }

    public function requiresBuyer(): bool
    {
        return $this !== self::Translate;
    }
}
