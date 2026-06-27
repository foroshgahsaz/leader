<?php

namespace App\Data\LeadFinder;

readonly class ImportLeadRowData
{
    public function __construct(
        public string $name,
        public string $countryCode,
        public ?string $city,
        public ?string $website,
        public ?string $industry,
        public ?string $companyType,
        public ?string $email,
        public ?string $phone,
    ) {}
}
