<?php

namespace App\Data\Auth;

readonly class RegisterOrganizationData
{
    public function __construct(
        public string $firstName,
        public string $lastName,
        public string $email,
        public string $password,
        public string $companyName,
        public string $countryCode,
        public ?string $website = null,
    ) {}

    public static function fromArray(array $data): self
    {
        return new self(
            firstName: $data['first_name'],
            lastName: $data['last_name'],
            email: $data['email'],
            password: $data['password'],
            companyName: $data['company_name'],
            countryCode: $data['country_code'],
            website: $data['website'] ?? null,
        );
    }
}
