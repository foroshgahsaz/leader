<?php

namespace App\Livewire\LeadFinder;

use App\Contracts\Repositories\BuyerRepositoryInterface;
use App\Enums\BuyerStatus;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Url;
use Livewire\Component;
use Livewire\WithPagination;

class SavedLeads extends Component
{
    use WithPagination;

    #[Url]
    public string $status = '';

    #[Url]
    public string $search = '';

    public function mount(): void
    {
        $this->authorize('viewAny', \App\Models\Buyer::class);
    }

    public function updatedStatus(): void
    {
        $this->resetPage();
    }

    public function updatedSearch(): void
    {
        $this->resetPage();
    }

    #[Layout('layouts.discover', ['heading' => 'Saved Leads', 'subheading' => 'Manage leads saved to your organization.'])]
    public function render(BuyerRepositoryInterface $buyerRepository): \Illuminate\Contracts\View\View
    {
        $query = \App\Models\Buyer::query()
            ->with(['owner', 'globalBuyer', 'currentScore', 'tags'])
            ->orderByDesc('last_activity_at')
            ->orderBy('name');

        if ($this->status !== '') {
            $query->where('status', $this->status);
        }

        if ($this->search !== '') {
            $term = '%'.$this->search.'%';
            $query->where(function ($builder) use ($term): void {
                $builder->where('name', 'like', $term)
                    ->orWhere('country_code', 'like', $term)
                    ->orWhere('industry', 'like', $term);
            });
        }

        return view('livewire.lead-finder.saved-leads', [
            'leads' => $query->paginate(25),
            'statuses' => BuyerStatus::options(),
        ]);
    }
}
