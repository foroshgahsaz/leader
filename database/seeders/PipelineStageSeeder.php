<?php

namespace Database\Seeders;

use App\Models\Organization;
use App\Services\Crm\PipelineStageService;
use Illuminate\Database\Seeder;

class PipelineStageSeeder extends Seeder
{
    public function run(PipelineStageService $pipelineStageService): void
    {
        Organization::query()->each(function (Organization $organization) use ($pipelineStageService): void {
            $pipelineStageService->ensureDefaults($organization);
        });
    }
}
