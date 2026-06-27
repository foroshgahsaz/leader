<div class="space-y-6">
    @if (session('status'))
        <div class="rounded-md bg-green-50 p-4 text-sm text-green-800">{{ session('status') }}</div>
    @endif
    @if (session('error'))
        <div class="rounded-md bg-red-50 p-4 text-sm text-red-800">{{ session('error') }}</div>
    @endif

    <div class="flex flex-wrap items-center justify-between gap-3">
        <div class="flex flex-wrap gap-2">
            <a href="{{ route('discover.index') }}" wire:navigate class="text-sm text-indigo-600 hover:text-indigo-800">{{ __('Search') }}</a>
            <span class="text-gray-300">|</span>
            <a href="{{ route('discover.saved') }}" wire:navigate class="text-sm text-indigo-600 hover:text-indigo-800">{{ __('Saved Leads') }}</a>
        </div>
        <div class="flex flex-wrap gap-2">
            @can('export', \App\Models\Buyer::class)
                <a href="{{ route('discover.export') }}" class="inline-flex items-center rounded-md border border-gray-300 bg-white px-3 py-2 text-sm font-medium text-gray-700 hover:bg-gray-50">
                    {{ __('Export CSV') }}
                </a>
            @endcan
        </div>
    </div>

    <form wire:submit="search" class="grid gap-4 md:grid-cols-2 lg:grid-cols-3">
        <div class="lg:col-span-3">
            <x-input-label for="query" :value="__('Search')" />
            <x-text-input wire:model="query" id="query" type="search" class="mt-1 block w-full" placeholder="{{ __('Company name, industry...') }}" />
        </div>

        <div>
            <x-input-label for="product" :value="__('Product / HS')" />
            <x-text-input wire:model="product" id="product" class="mt-1 block w-full" placeholder="{{ __('e.g. olive oil') }}" />
        </div>

        <div>
            <x-input-label for="countries" :value="__('Countries')" />
            <x-text-input wire:model="countries" id="countries" class="mt-1 block w-full" placeholder="{{ __('US, DE, TR') }}" />
        </div>

        <div>
            <x-input-label for="industry" :value="__('Industry')" />
            <x-text-input wire:model="industry" id="industry" class="mt-1 block w-full" />
        </div>

        <div>
            <x-input-label for="companyType" :value="__('Company Type')" />
            <select wire:model="companyType" id="companyType" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                <option value="">{{ __('Any') }}</option>
                @foreach ($companyTypes as $value => $label)
                    <option value="{{ $value }}">{{ $label }}</option>
                @endforeach
            </select>
        </div>

        <div>
            <x-input-label for="minScore" :value="__('Min Score')" />
            <x-text-input wire:model="minScore" id="minScore" type="number" min="0" max="100" class="mt-1 block w-full" />
        </div>

        <div>
            <x-input-label for="maxScore" :value="__('Max Score')" />
            <x-text-input wire:model="maxScore" id="maxScore" type="number" min="0" max="100" class="mt-1 block w-full" />
        </div>

        <div class="flex items-end gap-2 lg:col-span-3">
            <x-primary-button type="submit">{{ __('Search') }}</x-primary-button>
            <x-secondary-button type="button" wire:click="resetFilters">{{ __('Reset') }}</x-secondary-button>
        </div>
    </form>

    @if ($searched && $results)
        <div class="overflow-hidden border border-gray-200 rounded-lg">
            <table class="min-w-full divide-y divide-gray-200 text-sm">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-4 py-3 text-left font-medium text-gray-600">{{ __('Company') }}</th>
                        <th class="px-4 py-3 text-left font-medium text-gray-600">{{ __('Location') }}</th>
                        <th class="px-4 py-3 text-left font-medium text-gray-600">{{ __('Industry') }}</th>
                        <th class="px-4 py-3 text-left font-medium text-gray-600">{{ __('Score') }}</th>
                        <th class="px-4 py-3 text-left font-medium text-gray-600">{{ __('Import Activity') }}</th>
                        <th class="px-4 py-3 text-right font-medium text-gray-600">{{ __('Actions') }}</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-200 bg-white">
                    @forelse ($results as $result)
                        <tr wire:key="result-{{ $result->globalBuyerId }}">
                            <td class="px-4 py-3">
                                <div class="font-medium text-gray-900">{{ $result->name }}</div>
                                @if ($result->website)
                                    <div class="text-xs text-gray-500 truncate max-w-xs">{{ $result->website }}</div>
                                @endif
                            </td>
                            <td class="px-4 py-3 text-gray-600">
                                {{ $result->city ? $result->city.', ' : '' }}{{ $result->countryCode }}
                            </td>
                            <td class="px-4 py-3 text-gray-600">{{ $result->industry ?? '—' }}</td>
                            <td class="px-4 py-3">
                                @if ($result->score !== null)
                                    <span @class([
                                        'inline-flex rounded-full px-2 py-0.5 text-xs font-medium',
                                        'bg-green-100 text-green-800' => $result->scoreBand?->value === 'high',
                                        'bg-yellow-100 text-yellow-800' => $result->scoreBand?->value === 'medium',
                                        'bg-gray-100 text-gray-800' => $result->scoreBand?->value === 'low',
                                    ])>
                                        {{ $result->score }} · {{ $result->scoreBand?->label() }}
                                    </span>
                                @else
                                    —
                                @endif
                            </td>
                            <td class="px-4 py-3 text-gray-600">{{ $result->importActivityLevel }}/5</td>
                            <td class="px-4 py-3 text-right whitespace-nowrap">
                                @if ($result->isSaved && $result->buyerId)
                                    <a href="{{ route('discover.leads.show', $result->buyerId) }}" wire:navigate class="text-indigo-600 hover:text-indigo-800">{{ __('View') }}</a>
                                    @can('update', \App\Models\Buyer::find($result->buyerId))
                                        <button type="button" wire:click="toggleFavorite('{{ $result->buyerId }}')" class="ms-2 text-gray-500 hover:text-yellow-600" title="{{ __('Toggle favorite') }}">
                                            {{ $result->isFavorite ? '★' : '☆' }}
                                        </button>
                                    @endcan
                                @else
                                    @can('save', \App\Models\Buyer::class)
                                        <button type="button" wire:click="saveLead('{{ $result->globalBuyerId }}')" class="text-indigo-600 hover:text-indigo-800">
                                            {{ __('Save Lead') }}
                                        </button>
                                    @endcan
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="px-4 py-8 text-center text-gray-500">{{ __('No buyers matched your search.') }}</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        {{ $results->links() }}
    @elseif (! $searched)
        <p class="text-sm text-gray-500">{{ __('Enter search criteria and click Search to find global buyers.') }}</p>
    @endif

    @can('import', \App\Models\Buyer::class)
        <div class="border-t border-gray-200 pt-6">
            <h3 class="text-sm font-medium text-gray-900">{{ __('Import Leads') }}</h3>
            <form action="{{ route('discover.import.store') }}" method="POST" enctype="multipart/form-data" class="mt-3 flex flex-wrap items-end gap-3">
                @csrf
                <div>
                    <x-input-label for="file" :value="__('CSV file')" />
                    <input id="file" name="file" type="file" accept=".csv,.txt" required class="mt-1 block text-sm" />
                </div>
                <x-primary-button type="submit">{{ __('Upload') }}</x-primary-button>
            </form>
        </div>
    @endcan
</div>
