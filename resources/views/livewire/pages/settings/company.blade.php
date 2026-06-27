<?php

use App\Actions\Settings\UpdateCompanyProfileAction;
use App\Data\Settings\UpdateCompanyData;
use App\Support\OrganizationContext;
use Livewire\Attributes\Layout;
use Livewire\Volt\Component;

new
#[Layout('layouts.settings', ['heading' => 'Company Profile', 'subheading' => 'Manage your exporter company details used across ExportOS.'])]
class extends Component
{
    public string $name = '';

    public string $country_code = '';

    public ?string $website = null;

    public ?string $industry = null;

    public string $timezone = 'UTC';

    public function mount(OrganizationContext $organizationContext): void
    {
        $this->authorize('view', $organizationContext->get());

        $organization = $organizationContext->get();

        $this->name = $organization->name;
        $this->country_code = $organization->country_code;
        $this->website = $organization->website;
        $this->industry = $organization->industry;
        $this->timezone = $organization->timezone;
    }

    public function save(UpdateCompanyProfileAction $updateCompanyProfileAction, OrganizationContext $organizationContext): void
    {
        $organization = $organizationContext->get();
        $this->authorize('update', $organization);

        $validated = $this->validate([
            'name' => ['required', 'string', 'max:255'],
            'country_code' => ['required', 'string', 'size:2'],
            'website' => ['nullable', 'url', 'max:500'],
            'industry' => ['nullable', 'string', 'max:100'],
            'timezone' => ['required', 'string', 'max:64'],
        ]);

        $updateCompanyProfileAction->execute(
            $organization,
            UpdateCompanyData::fromArray($validated),
            auth()->user(),
        );

        $this->dispatch('saved');
    }
}; ?>

<form wire:submit="save" class="space-y-6 max-w-2xl">
    <div>
        <x-input-label for="name" :value="__('Company name')" />
        <x-text-input wire:model="name" id="name" class="mt-1 block w-full" type="text" required />
        <x-input-error class="mt-2" :messages="$errors->get('name')" />
    </div>

    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
        <div>
            <x-input-label for="country_code" :value="__('Country code')" />
            <x-text-input wire:model="country_code" id="country_code" class="mt-1 block w-full uppercase" maxlength="2" required />
            <x-input-error class="mt-2" :messages="$errors->get('country_code')" />
        </div>
        <div>
            <x-input-label for="timezone" :value="__('Timezone')" />
            <x-text-input wire:model="timezone" id="timezone" class="mt-1 block w-full" required />
            <x-input-error class="mt-2" :messages="$errors->get('timezone')" />
        </div>
    </div>

    <div>
        <x-input-label for="website" :value="__('Website')" />
        <x-text-input wire:model="website" id="website" class="mt-1 block w-full" type="url" />
        <x-input-error class="mt-2" :messages="$errors->get('website')" />
    </div>

    <div>
        <x-input-label for="industry" :value="__('Industry')" />
        <x-text-input wire:model="industry" id="industry" class="mt-1 block w-full" type="text" />
        <x-input-error class="mt-2" :messages="$errors->get('industry')" />
    </div>

    <div class="flex items-center gap-4">
        <x-primary-button>{{ __('Save company') }}</x-primary-button>
        <x-action-message on="saved">{{ __('Saved.') }}</x-action-message>
    </div>
</form>
