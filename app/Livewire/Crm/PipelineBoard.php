<?php

namespace App\Livewire\Crm;

use App\Actions\Crm\ChangeDealStageAction;
use App\Data\Crm\ChangeDealStageData;
use App\Models\Deal;
use App\Models\PipelineStage;
use App\Services\Crm\PipelineStageService;
use App\Support\OrganizationContext;
use Livewire\Attributes\Layout;
use Livewire\Component;

class PipelineBoard extends Component
{
    public ?string $draggingDealId = null;

    public function mount(): void
    {
        abort_unless(auth()->user()->can('crm.pipeline.view'), 403);
    }

    public function changeStage(string $dealId, string $stageId, ChangeDealStageAction $changeDealStageAction): void
    {
        $deal = Deal::query()->with('stage')->findOrFail($dealId);
        $this->authorize('changeStage', $deal);

        $changeDealStageAction->execute(
            new ChangeDealStageData(dealId: $dealId, stageId: $stageId),
            auth()->user(),
        );

        session()->flash('status', __('Deal stage updated.'));
    }

    #[Layout('layouts.crm', ['heading' => 'Pipeline', 'subheading' => 'Drag deals between stages to update progress.'])]
    public function render(PipelineStageService $pipelineStageService, OrganizationContext $organizationContext)
    {
        $organization = $organizationContext->get();
        if ($organization) {
            $pipelineStageService->ensureDefaults($organization);
        }

        $stages = PipelineStage::query()
            ->with(['deals' => fn ($query) => $query->with(['buyer', 'owner'])->orderBy('updated_at', 'desc')])
            ->orderBy('sort_order')
            ->get();

        return view('livewire.crm.pipeline-board', [
            'stages' => $stages,
        ]);
    }
}
