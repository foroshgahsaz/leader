<?php

namespace App\Listeners\Crm;

use App\Events\Auth\OrganizationRegistered;
use App\Services\Crm\PipelineStageService;

class EnsurePipelineStagesForOrganization
{
    public function __construct(
        protected PipelineStageService $pipelineStageService,
    ) {}

    public function handle(OrganizationRegistered $event): void
    {
        $this->pipelineStageService->ensureDefaults($event->organization);
    }
}
