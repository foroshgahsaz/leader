<?php

namespace App\Services\Crm;

use App\Models\Organization;
use App\Models\PipelineStage;

class PipelineStageService
{
    /**
     * @return array<int, array{key: string, label: string, sort_order: int, is_closed: bool, is_won: bool, color: string}>
     */
    public function defaultStages(): array
    {
        return [
            ['key' => 'new', 'label' => 'New', 'sort_order' => 1, 'is_closed' => false, 'is_won' => false, 'color' => 'gray'],
            ['key' => 'contacted', 'label' => 'Contacted', 'sort_order' => 2, 'is_closed' => false, 'is_won' => false, 'color' => 'blue'],
            ['key' => 'qualified', 'label' => 'Qualified', 'sort_order' => 3, 'is_closed' => false, 'is_won' => false, 'color' => 'indigo'],
            ['key' => 'meeting', 'label' => 'Meeting', 'sort_order' => 4, 'is_closed' => false, 'is_won' => false, 'color' => 'purple'],
            ['key' => 'proposal', 'label' => 'Proposal', 'sort_order' => 5, 'is_closed' => false, 'is_won' => false, 'color' => 'yellow'],
            ['key' => 'negotiation', 'label' => 'Negotiation', 'sort_order' => 6, 'is_closed' => false, 'is_won' => false, 'color' => 'orange'],
            ['key' => 'won', 'label' => 'Won', 'sort_order' => 7, 'is_closed' => true, 'is_won' => true, 'color' => 'green'],
            ['key' => 'lost', 'label' => 'Lost', 'sort_order' => 8, 'is_closed' => true, 'is_won' => false, 'color' => 'red'],
        ];
    }

    public function ensureDefaults(Organization $organization): void
    {
        if (PipelineStage::query()->where('organization_id', $organization->id)->exists()) {
            return;
        }

        foreach ($this->defaultStages() as $stage) {
            PipelineStage::query()->create([
                'organization_id' => $organization->id,
                ...$stage,
            ]);
        }
    }

    public function defaultStage(Organization $organization): ?PipelineStage
    {
        $this->ensureDefaults($organization);

        return PipelineStage::query()
            ->where('organization_id', $organization->id)
            ->orderBy('sort_order')
            ->first();
    }
}
