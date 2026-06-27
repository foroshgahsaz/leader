<?php

namespace App\Data\LeadFinder;

use App\Enums\LeadSource;

readonly class SaveBuyerData
{
    public function __construct(
        public string $globalBuyerId,
        public LeadSource $source,
    ) {}
}
