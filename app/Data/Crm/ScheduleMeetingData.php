<?php

namespace App\Data\Crm;

use DateTimeInterface;

readonly class ScheduleMeetingData
{
    public function __construct(
        public string $buyerId,
        public string $title,
        public DateTimeInterface $startsAt,
        public DateTimeInterface $endsAt,
        public ?string $agenda = null,
        public ?string $location = null,
        public ?string $meetingUrl = null,
        public ?string $dealId = null,
        public ?string $contactId = null,
    ) {}
}
