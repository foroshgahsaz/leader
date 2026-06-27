<?php

namespace App\Data\Crm;

readonly class ChangeDealStageData
{
    public function __construct(
        public string $dealId,
        public string $stageId,
        public ?string $reason = null,
        public ?string $lostReason = null,
        public ?string $lostNotes = null,
    ) {}
}
