<?php

namespace App\Livewire\LeadFinder;

use App\Actions\LeadFinder\RunBuyerSearchAction;
use App\Actions\LeadFinder\SaveBuyerAction;
use App\Actions\LeadFinder\ToggleFavoriteAction;
use App\Data\LeadFinder\BuyerSearchCriteriaData;
use App\Data\LeadFinder\SaveBuyerData;
use App\Enums\CompanyType;
use App\Enums\LeadSource;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Url;
use Livewire\Component;
use Livewire\WithPagination;

class LeadSearch extends Component
{
    use WithPagination;

    #[Url(as: 'q')]
    public string $query = '';

    #[Url]
    public string $product = '';

    #[Url]
    public string $countries = '';

    #[Url]
    public string $industry = '';

    #[Url]
    public string $companyType = '';

    #[Url]
    public ?int $minScore = null;

    #[Url]
    public ?int $maxScore = null;

    #[Url]
    public string $sortBy = 'import_activity_level';

    #[Url]
    public string $sortDirection = 'desc';

    public int $perPage = 25;

    public bool $searched = false;

    public function mount(): void
    {
        $this->authorize('search', \App\Models\Buyer::class);

        if ($this->hasActiveFilters()) {
            $this->searched = true;
        }
    }

    public function search(): void
    {
        $this->resetPage();
        $this->searched = true;
    }

    public function resetFilters(): void
    {
        $this->reset([
            'query',
            'product',
            'countries',
            'industry',
            'companyType',
            'minScore',
            'maxScore',
            'sortBy',
            'sortDirection',
        ]);
        $this->resetPage();
        $this->searched = false;
    }

    public function saveLead(string $globalBuyerId, SaveBuyerAction $saveBuyerAction): void
    {
        $this->authorize('save', \App\Models\Buyer::class);

        try {
            $buyer = $saveBuyerAction->execute(
                new SaveBuyerData($globalBuyerId, LeadSource::Search),
                auth()->user(),
            );

            session()->flash('status', __('Lead saved successfully.'));

            $this->redirectRoute('discover.leads.show', $buyer, navigate: true);
        } catch (\DomainException $exception) {
            session()->flash('error', $exception->getMessage());
        }
    }

    public function toggleFavorite(string $buyerId, ToggleFavoriteAction $toggleFavoriteAction): void
    {
        $buyer = \App\Models\Buyer::query()->findOrFail($buyerId);
        $this->authorize('update', $buyer);

        $toggleFavoriteAction->execute($buyerId, auth()->user());
    }

    protected function hasActiveFilters(): bool
    {
        return $this->query !== ''
            || $this->product !== ''
            || $this->countries !== ''
            || $this->industry !== ''
            || $this->companyType !== ''
            || $this->minScore !== null
            || $this->maxScore !== null;
    }

    protected function criteria(): BuyerSearchCriteriaData
    {
        $countries = array_values(array_filter(array_map(
            fn (string $code) => strtoupper(trim($code)),
            explode(',', $this->countries),
        )));

        return new BuyerSearchCriteriaData(
            product: $this->product !== '' ? $this->product : null,
            countries: $countries,
            industry: $this->industry !== '' ? $this->industry : null,
            companyType: $this->companyType !== '' ? $this->companyType : null,
            minScore: $this->minScore,
            maxScore: $this->maxScore,
            query: $this->query !== '' ? $this->query : null,
            sortBy: $this->sortBy,
            sortDirection: $this->sortDirection,
            perPage: $this->perPage,
        );
    }

    #[Layout('layouts.discover', ['heading' => 'Lead Finder', 'subheading' => 'Search global buyers and save promising leads to your pipeline.'])]
    public function render(RunBuyerSearchAction $runBuyerSearchAction): \Illuminate\Contracts\View\View
    {
        /** @var LengthAwarePaginator|null $results */
        $results = null;

        if ($this->searched) {
            $results = $runBuyerSearchAction->execute(
                $this->criteria(),
                auth()->user(),
            );
        }

        return view('livewire.lead-finder.lead-search', [
            'results' => $results,
            'companyTypes' => CompanyType::options(),
        ]);
    }
}
