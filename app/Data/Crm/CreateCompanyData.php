<?php

namespace App\Data\Crm;

use App\Enums\CompanyType;
use App\Enums\LeadSource;

readonly class CreateCompanyData
{
    public function __construct(
        public string $name,
        public string $countryCode,
        public ?string $city = null,
        public ?string $website = null,
        public ?string $phone = null,
        public ?string $industry = null,
        public ?string $description = null,
        public ?CompanyType $companyType = null,
        public ?int $ownerId = null,
        public LeadSource $source = LeadSource::Manual,
    ) {}
}
