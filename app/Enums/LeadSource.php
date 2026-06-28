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
        return __('enums.lead_source.'.$this->value);
    }
}
