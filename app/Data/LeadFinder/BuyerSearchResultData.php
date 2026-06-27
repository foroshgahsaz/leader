<?php

namespace App\Data\LeadFinder;

use App\Enums\ScoreBand;

readonly class BuyerSearchResultData
{
    public function __construct(
        public string $globalBuyerId,
        public ?string $buyerId,
        public string $name,
        public string $countryCode,
        public ?string $city,
        public ?string $industry,
        public ?string $companyType,
        public ?int $score,
        public ?ScoreBand $scoreBand,
        public int $importActivityLevel,
        public bool $isSaved,
        public bool $isFavorite,
        public ?string $website,
    ) {}
}
