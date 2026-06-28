<?php

namespace App\Enums;

enum ScoreBand: string
{
    case High = 'high';
    case Medium = 'medium';
    case Low = 'low';

    public function label(): string
    {
        return __('enums.score_band.'.$this->value);
    }

    public static function fromScore(int $score): self
    {
        if ($score >= 80) {
            return self::High;
        }

        if ($score >= 50) {
            return self::Medium;
        }

        return self::Low;
    }
}
