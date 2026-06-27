<?php

namespace App\Data\Crm;

use App\Enums\CrmActivityType;
use DateTimeInterface;

readonly class LogCrmActivityData
{
    public function __construct(
        public string $buyerId,
        public CrmActivityType $activityType,
        public string $subject,
        public ?string $body = null,
        public ?int $durationMinutes = null,
        public ?DateTimeInterface $occurredAt = null,
        public ?string $dealId = null,
        public ?string $contactId = null,
    ) {}
}
