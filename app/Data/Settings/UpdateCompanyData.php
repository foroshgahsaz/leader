<?php

namespace App\Data\Settings;

readonly class UpdateCompanyData
{
    public function __construct(
        public string $name,
        public string $countryCode,
        public ?string $website,
        public ?string $industry,
        public string $timezone,
    ) {}

    public static function fromArray(array $data): self
    {
        return new self(
            name: $data['name'],
            countryCode: $data['country_code'],
            website: $data['website'] ?? null,
            industry: $data['industry'] ?? null,
            timezone: $data['timezone'],
        );
    }
}
