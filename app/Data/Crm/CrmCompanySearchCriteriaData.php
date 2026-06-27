<?php

namespace App\Data\Crm;

readonly class CrmCompanySearchCriteriaData
{
    public function __construct(
        public ?string $query = null,
        public ?int $ownerId = null,
        public ?string $pipelineStage = null,
        public ?string $countryCode = null,
        public ?string $status = null,
        public ?string $industry = null,
        public ?bool $hasDeal = null,
        public ?string $sortBy = 'last_activity_at',
        public ?string $sortDirection = 'desc',
        public int $perPage = 25,
    ) {}
}
