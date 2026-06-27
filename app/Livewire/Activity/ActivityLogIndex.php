<?php

namespace App\Livewire\Activity;

use App\Models\ActivityLog;
use App\Support\OrganizationContext;
use Livewire\Attributes\Layout;
use Livewire\Component;
use Livewire\WithPagination;

class ActivityLogIndex extends Component
{
    use WithPagination;

    public string $search = '';

    public function mount(): void
    {
        $this->authorize('view-activity-logs');
    }

    public function updatedSearch(): void
    {
        $this->resetPage();
    }

    #[Layout('layouts.settings', ['heading' => 'Activity Log', 'subheading' => 'Review recent actions across your organization.'])]
    public function render(OrganizationContext $organizationContext)
    {
        $query = ActivityLog::query()
            ->with('actor')
            ->where('organization_id', $organizationContext->id())
            ->latest('occurred_at');

        if ($this->search !== '') {
            $query->where('summary', 'like', '%'.$this->search.'%');
        }

        return view('livewire.activity.activity-log-index', [
            'activities' => $query->paginate(20),
        ]);
    }
}
