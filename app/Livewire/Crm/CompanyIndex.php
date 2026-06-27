<?php

namespace App\Livewire\Crm;

use App\Actions\Crm\CreateCompanyAction;
use App\Contracts\Repositories\CompanyRepositoryInterface;
use App\Data\Crm\CreateCompanyData;
use App\Data\Crm\CrmCompanySearchCriteriaData;
use App\Enums\BuyerStatus;
use App\Enums\OrgMemberStatus;
use App\Models\OrgMember;
use App\Models\PipelineStage;
use App\Models\User;
use App\Services\Crm\PipelineStageService;
use App\Support\OrganizationContext;
use Livewire\Attributes\Layout;
use Livewire\Component;
use Livewire\WithPagination;

class CompanyIndex extends Component
{
    use WithPagination;

    public string $search = '';

    public string $ownerId = '';

    public string $pipelineStage = '';

    public string $countryCode = '';

    public string $status = '';

    public bool $showCreateModal = false;

    public string $name = '';

    public string $newCountryCode = '';

    public string $city = '';

    public string $website = '';

    public string $phone = '';

    public string $industry = '';

    public string $description = '';

    public function mount(PipelineStageService $pipelineStageService, OrganizationContext $organizationContext): void
    {
        $this->authorize('viewAny', \App\Models\Buyer::class);

        $organization = $organizationContext->get();
        if ($organization) {
            $pipelineStageService->ensureDefaults($organization);
        }
    }

    public function updatedSearch(): void
    {
        $this->resetPage();
    }

    public function updatedOwnerId(): void
    {
        $this->resetPage();
    }

    public function updatedPipelineStage(): void
    {
        $this->resetPage();
    }

    public function updatedCountryCode(): void
    {
        $this->resetPage();
    }

    public function updatedStatus(): void
    {
        $this->resetPage();
    }

    public function resetFilters(): void
    {
        $this->reset(['search', 'ownerId', 'pipelineStage', 'countryCode', 'status']);
        $this->resetPage();
    }

    public function openCreateModal(): void
    {
        $this->authorize('create', \App\Models\Buyer::class);
        $this->showCreateModal = true;
    }

    public function createCompany(CreateCompanyAction $createCompanyAction): void
    {
        $this->authorize('create', \App\Models\Buyer::class);

        $this->validate([
            'name' => ['required', 'string', 'max:255'],
            'newCountryCode' => ['required', 'string', 'size:2'],
            'city' => ['nullable', 'string', 'max:100'],
            'website' => ['nullable', 'url', 'max:255'],
            'phone' => ['nullable', 'string', 'max:50'],
            'industry' => ['nullable', 'string', 'max:100'],
            'description' => ['nullable', 'string', 'max:2000'],
        ]);

        $company = $createCompanyAction->execute(
            new CreateCompanyData(
                name: $this->name,
                countryCode: $this->newCountryCode,
                city: $this->city !== '' ? $this->city : null,
                website: $this->website !== '' ? $this->website : null,
                phone: $this->phone !== '' ? $this->phone : null,
                industry: $this->industry !== '' ? $this->industry : null,
                description: $this->description !== '' ? $this->description : null,
            ),
            auth()->user(),
        );

        $this->reset(['name', 'newCountryCode', 'city', 'website', 'phone', 'industry', 'description', 'showCreateModal']);
        session()->flash('status', __('Company created.'));

        $this->redirect(route('crm.companies.show', $company), navigate: true);
    }

    #[Layout('layouts.crm', ['heading' => 'Companies', 'subheading' => 'Manage your CRM companies and pipeline.'])]
    public function render(
        CompanyRepositoryInterface $companyRepository,
        OrganizationContext $organizationContext,
    ) {
        $companies = $companyRepository->search(new CrmCompanySearchCriteriaData(
            query: $this->search !== '' ? $this->search : null,
            ownerId: $this->ownerId !== '' ? (int) $this->ownerId : null,
            pipelineStage: $this->pipelineStage !== '' ? $this->pipelineStage : null,
            countryCode: $this->countryCode !== '' ? $this->countryCode : null,
            status: $this->status !== '' ? $this->status : null,
        ));

        $organizationId = $organizationContext->id();

        $owners = User::query()
            ->whereIn('id', OrgMember::query()
                ->where('organization_id', $organizationId)
                ->where('status', OrgMemberStatus::Active)
                ->pluck('user_id'))
            ->orderBy('first_name')
            ->get();

        $stages = PipelineStage::query()->orderBy('sort_order')->get();

        return view('livewire.crm.company-index', [
            'companies' => $companies,
            'owners' => $owners,
            'stages' => $stages,
            'statuses' => BuyerStatus::options(),
        ]);
    }
}
