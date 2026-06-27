<?php

namespace App\Data\Crm;

use DateTimeInterface;

readonly class CreateCrmTaskData
{
    public function __construct(
        public string $title,
        public DateTimeInterface $dueAt,
        public ?string $buyerId = null,
        public ?string $dealId = null,
        public ?string $description = null,
        public string $priority = 'medium',
        public string $taskType = 'general',
        public ?int $assignedToUserId = null,
    ) {}
}
