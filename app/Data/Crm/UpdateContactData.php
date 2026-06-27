<?php

namespace App\Data\Crm;

readonly class UpdateContactData
{
    public function __construct(
        public string $fullName,
        public ?string $title = null,
        public ?string $email = null,
        public ?string $phone = null,
        public bool $isPrimary = false,
    ) {}
}
