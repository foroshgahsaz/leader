<?php

namespace App\Data\LeadFinder;

readonly class CreateBuyerNoteData
{
    public function __construct(
        public string $body,
        public bool $isPinned = false,
    ) {}
}
