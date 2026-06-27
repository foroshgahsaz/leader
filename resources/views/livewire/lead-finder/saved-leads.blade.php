<div class="space-y-6">
    <div class="flex flex-wrap items-center justify-between gap-3">
        <div class="flex flex-wrap gap-2">
            <a href="{{ route('discover.index') }}" wire:navigate class="text-sm text-indigo-600 hover:text-indigo-800">{{ __('Search') }}</a>
            <span class="text-gray-300">|</span>
            <span class="text-sm text-gray-600">{{ __('Saved Leads') }}</span>
        </div>
        @can('export', \App\Models\Buyer::class)
            <a href="{{ route('discover.export') }}" class="inline-flex items-center rounded-md border border-gray-300 bg-white px-3 py-2 text-sm font-medium text-gray-700 hover:bg-gray-50">
                {{ __('Export CSV') }}
            </a>
        @endcan
    </div>

    <div class="grid gap-4 md:grid-cols-3">
        <div>
            <x-input-label for="search" :value="__('Filter by name')" />
            <x-text-input wire:model.live.debounce.300ms="search" id="search" type="search" class="mt-1 block w-full" />
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

    <div class="overflow-hidden border border-gray-200 rounded-lg">
        <table class="min-w-full divide-y divide-gray-200 text-sm">
            <thead class="bg-gray-50">
                <tr>
                    <th class="px-4 py-3 text-left font-medium text-gray-600">{{ __('Company') }}</th>
                    <th class="px-4 py-3 text-left font-medium text-gray-600">{{ __('Location') }}</th>
                    <th class="px-4 py-3 text-left font-medium text-gray-600">{{ __('Status') }}</th>
                    <th class="px-4 py-3 text-left font-medium text-gray-600">{{ __('Score') }}</th>
                    <th class="px-4 py-3 text-left font-medium text-gray-600">{{ __('Tags') }}</th>
                    <th class="px-4 py-3 text-right font-medium text-gray-600">{{ __('Actions') }}</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-200 bg-white">
                @forelse ($leads as $lead)
                    <tr wire:key="lead-{{ $lead->id }}">
                        <td class="px-4 py-3">
                            <div class="font-medium text-gray-900">
                                @if ($lead->is_favorite)
                                    <span class="text-yellow-500">★</span>
                                @endif
                                {{ $lead->name }}
                            </div>
                        </td>
                        <td class="px-4 py-3 text-gray-600">{{ $lead->city ? $lead->city.', ' : '' }}{{ $lead->country_code }}</td>
                        <td class="px-4 py-3">
                            <span class="inline-flex rounded-full bg-gray-100 px-2 py-0.5 text-xs font-medium text-gray-800">
                                {{ $lead->status->label() }}
                            </span>
                        </td>
                        <td class="px-4 py-3 text-gray-600">
                            {{ $lead->currentScore?->score ?? '—' }}
                        </td>
                        <td class="px-4 py-3">
                            <div class="flex flex-wrap gap-1">
                                @foreach ($lead->tags as $tag)
                                    <span class="rounded bg-indigo-50 px-2 py-0.5 text-xs text-indigo-700">{{ $tag->tag }}</span>
                                @endforeach
                            </div>
                        </td>
                        <td class="px-4 py-3 text-right">
                            <a href="{{ route('discover.leads.show', $lead) }}" wire:navigate class="text-indigo-600 hover:text-indigo-800">{{ __('View') }}</a>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="6" class="px-4 py-8 text-center text-gray-500">{{ __('No saved leads yet. Search and save promising buyers.') }}</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    {{ $leads->links() }}
</div>
