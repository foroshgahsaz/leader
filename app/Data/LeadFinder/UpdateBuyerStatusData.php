<?php

namespace App\Data\LeadFinder;

use App\Enums\BuyerStatus;

readonly class UpdateBuyerStatusData
{
    public function __construct(
        public BuyerStatus $status,
        public ?string $reason = null,
    ) {}
}
