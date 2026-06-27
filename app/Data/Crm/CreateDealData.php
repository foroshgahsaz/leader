<?php

namespace App\Data\Crm;

readonly class CreateDealData
{
    public function __construct(
        public string $buyerId,
        public string $title,
        public ?string $stageId = null,
        public ?int $ownerId = null,
        public ?float $estimatedValue = null,
        public string $currencyCode = 'USD',
        public ?string $expectedCloseDate = null,
    ) {}
}
