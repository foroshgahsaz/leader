<?php

namespace App\Data\Settings;

readonly class UpdateUserProfileData
{
    public function __construct(
        public string $firstName,
        public string $lastName,
        public ?string $jobTitle,
        public string $email,
        public ?string $timezone,
        public string $locale,
    ) {}

    public static function fromArray(array $data): self
    {
        return new self(
            firstName: $data['first_name'],
            lastName: $data['last_name'],
            jobTitle: $data['job_title'] ?? null,
            email: $data['email'],
            timezone: $data['timezone'] ?? null,
            locale: $data['locale'],
        );
    }
}
