<?php

namespace App\Enums;

enum ScoreBand: string
{
    case High = 'high';
    case Medium = 'medium';
    case Low = 'low';

    public function label(): string
    {
        return match ($this) {
            self::High => 'High',
            self::Medium => 'Medium',
            self::Low => 'Low',
        };
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
