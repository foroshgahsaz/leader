<?php

namespace App\Enums;

enum LeadSource: string
{
    case Search = 'search';
    case Import = 'import';
    case Manual = 'manual';
    case Recommendation = 'recommendation';

    public function label(): string
    {
        return match ($this) {
            self::Search => 'Search',
            self::Import => 'Import',
            self::Manual => 'Manual',
            self::Recommendation => 'Recommendation',
        };
    }
}
