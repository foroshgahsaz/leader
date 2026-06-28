<div class="space-y-6">
    @if (session('status'))
        <div class="rounded-md bg-green-50 p-4 text-sm text-green-800">{{ session('status') }}</div>
    @endif
    @if (session('error'))
        <div class="rounded-md bg-red-50 p-4 text-sm text-red-800">{{ session('error') }}</div>
    @endif

    <div class="flex flex-wrap items-center justify-between gap-3">
        <div class="flex flex-wrap gap-2">
            <a href="{{ route('crm.companies.index') }}" wire:navigate class="text-sm text-indigo-600 hover:text-indigo-800">{{ __('Companies') }}</a>
            <span class="text-gray-300">|</span>
            <a href="{{ route('crm.pipeline') }}" wire:navigate class="text-sm text-indigo-600 hover:text-indigo-800">{{ __('Pipeline') }}</a>
            <span class="text-gray-300">|</span>
            <a href="{{ route('crm.tasks') }}" wire:navigate class="text-sm text-indigo-600 hover:text-indigo-800">{{ __('Tasks') }}</a>
            @can('crm.reports.view')
                <span class="text-gray-300">|</span>
                <a href="{{ route('crm.reports') }}" wire:navigate class="text-sm text-indigo-600 hover:text-indigo-800">{{ __('Reports') }}</a>
            @endcan
        </div>
        @can('create', \App\Models\Buyer::class)
            <x-primary-button type="button" wire:click="openCreateModal">{{ __('New Company') }}</x-primary-button>
        @endcan
    </div>

    <div class="grid gap-4 md:grid-cols-2 lg:grid-cols-5">
        <div>
            <x-input-label for="search" :value="__('Search')" />
            <x-text-input wire:model.live.debounce.300ms="search" id="search" type="search" class="mt-1 block w-full" placeholder="{{ __('Name, industry...') }}" />
        </div>
        <div>
            <x-input-label for="ownerId" :value="__('Owner')" />
            <select wire:model.live="ownerId" id="ownerId" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                <option value="">{{ __('All owners') }}</option>
                @foreach ($owners as $owner)
                    <option value="{{ $owner->id }}">{{ $owner->fullName() }}</option>
                @endforeach
            </select>
        </div>
        <div>
            <x-input-label for="pipelineStage" :value="__('Pipeline Stage')" />
            <select wire:model.live="pipelineStage" id="pipelineStage" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                <option value="">{{ __('All stages') }}</option>
                @foreach ($stages as $stage)
                    <option value="{{ $stage->key }}">{{ $stage->localizedLabel() }}</option>
                @endforeach
            </select>
        </div>
        <div>
            <x-input-label for="countryCode" :value="__('Country')" />
            <x-text-input wire:model.live.debounce.300ms="countryCode" id="countryCode" class="mt-1 block w-full" placeholder="{{ __('US') }}" maxlength="2" />
        </div>
        <div>
            <x-input-label for="status" :value="__('Status')" />
            <select wire:model.live="status" id="status" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                <option value="">{{ __('All statuses') }}</option>
                @foreach ($statuses as $value => $label)
                    <option value="{{ $value }}">{{ $label }}</option>
                @endforeach
            </select>
        </div>
    </div>

    <div>
        <x-secondary-button type="button" wire:click="resetFilters">{{ __('Reset filters') }}</x-secondary-button>
    </div>

    <div class="overflow-hidden border border-gray-200 rounded-lg">
        <table class="min-w-full divide-y divide-gray-200 text-sm">
            <thead class="bg-gray-50">
                <tr>
                    <th class="px-4 py-3 text-left font-medium text-gray-600">{{ __('Company') }}</th>
                    <th class="px-4 py-3 text-left font-medium text-gray-600">{{ __('Location') }}</th>
                    <th class="px-4 py-3 text-left font-medium text-gray-600">{{ __('Owner') }}</th>
                    <th class="px-4 py-3 text-left font-medium text-gray-600">{{ __('Stage') }}</th>
                    <th class="px-4 py-3 text-left font-medium text-gray-600">{{ __('Deal') }}</th>
                    <th class="px-4 py-3 text-left font-medium text-gray-600">{{ __('Last Activity') }}</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-200 bg-white">
                @forelse ($companies as $company)
                    <tr wire:key="company-{{ $company->id }}">
                        <td class="px-4 py-3">
                            <a href="{{ route('crm.companies.show', $company) }}" wire:navigate class="font-medium text-indigo-600 hover:text-indigo-800">{{ $company->name }}</a>
                            @if ($company->industry)
                                <div class="text-xs text-gray-500">{{ $company->industry }}</div>
                            @endif
                        </td>
                        <td class="px-4 py-3 text-gray-600">
                            {{ trim(($company->city ? $company->city.', ' : '').$company->country_code) ?: '—' }}
                        </td>
                        <td class="px-4 py-3 text-gray-600">{{ $company->owner?->fullName() ?? '—' }}</td>
                        <td class="px-4 py-3">
                            <span class="inline-flex rounded-full bg-gray-100 px-2 py-0.5 text-xs font-medium text-gray-800">
                                {{ $company->deal?->stage?->localizedLabel() ?? __('pipeline.' . ($company->pipeline_stage ?? 'new')) }}
                            </span>
                        </td>
                        <td class="px-4 py-3 text-gray-600">
                            @if ($company->deal)
                                {{ number_format((float) $company->deal->estimated_value, 0) }} {{ $company->deal->currency_code }}
                            @else
                                —
                            @endif
                        </td>
                        <td class="px-4 py-3 text-gray-600">{{ $company->last_activity_at?->diffForHumans() ?? '—' }}</td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="6" class="px-4 py-8 text-center text-gray-500">{{ __('No companies found.') }}</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    {{ $companies->links() }}

    @if ($showCreateModal)
        <div class="fixed inset-0 z-50 flex items-center justify-center bg-gray-900/50 p-4">
            <div class="w-full max-w-lg rounded-lg bg-white p-6 shadow-xl">
                <h3 class="text-lg font-medium text-gray-900">{{ __('New Company') }}</h3>
                <form wire:submit="createCompany" class="mt-4 space-y-4">
                    <div>
                        <x-input-label for="name" :value="__('Name')" />
                        <x-text-input wire:model="name" id="name" class="mt-1 block w-full" />
                        @error('name') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
                    </div>
                    <div class="grid gap-4 sm:grid-cols-2">
                        <div>
                            <x-input-label for="newCountryCode" :value="__('Country Code')" />
                            <x-text-input wire:model="newCountryCode" id="newCountryCode" class="mt-1 block w-full" maxlength="2" />
                            @error('newCountryCode') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
                        </div>
                        <div>
                            <x-input-label for="city" :value="__('City')" />
                            <x-text-input wire:model="city" id="city" class="mt-1 block w-full" />
                        </div>
                    </div>
                    <div>
                        <x-input-label for="industry" :value="__('Industry')" />
                        <x-text-input wire:model="industry" id="industry" class="mt-1 block w-full" />
                    </div>
                    <div>
                        <x-input-label for="website" :value="__('Website')" />
                        <x-text-input wire:model="website" id="website" type="url" class="mt-1 block w-full" />
                    </div>
                    <div>
                        <x-input-label for="phone" :value="__('Phone')" />
                        <x-text-input wire:model="phone" id="phone" class="mt-1 block w-full" />
                    </div>
                    <div>
                        <x-input-label for="description" :value="__('Description')" />
                        <textarea wire:model="description" id="description" rows="3" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500"></textarea>
                    </div>
                    <div class="flex justify-end gap-2">
                        <x-secondary-button type="button" wire:click="$set('showCreateModal', false)">{{ __('Cancel') }}</x-secondary-button>
                        <x-primary-button type="submit">{{ __('Create') }}</x-primary-button>
                    </div>
                </form>
            </div>
        </div>
    @endif
</div>
