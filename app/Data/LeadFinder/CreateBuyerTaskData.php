<?php

namespace App\Data\LeadFinder;

use App\Enums\TaskPriority;
use DateTimeInterface;

readonly class CreateBuyerTaskData
{
    public function __construct(
        public string $title,
        public ?string $description,
        public DateTimeInterface $dueAt,
        public TaskPriority $priority,
    ) {}
}
