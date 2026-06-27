<div class="space-y-4">
    <div>
        <x-text-input wire:model.live.debounce.300ms="search" type="search" class="w-full max-w-md" placeholder="{{ __('Search activity...') }}" />
    </div>

    <div class="overflow-hidden border border-gray-200 rounded-lg">
        <table class="min-w-full divide-y divide-gray-200 text-sm">
            <thead class="bg-gray-50">
                <tr>
                    <th class="px-4 py-3 text-left font-medium text-gray-600">{{ __('When') }}</th>
                    <th class="px-4 py-3 text-left font-medium text-gray-600">{{ __('Actor') }}</th>
                    <th class="px-4 py-3 text-left font-medium text-gray-600">{{ __('Action') }}</th>
                    <th class="px-4 py-3 text-left font-medium text-gray-600">{{ __('Summary') }}</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-200 bg-white">
                @forelse ($activities as $activity)
                    <tr wire:key="activity-{{ $activity->id }}">
                        <td class="px-4 py-3 whitespace-nowrap text-gray-500">{{ $activity->occurred_at->format('M j, Y g:i A') }}</td>
                        <td class="px-4 py-3">{{ $activity->actor?->fullName() ?? __('System') }}</td>
                        <td class="px-4 py-3 capitalize">{{ $activity->action->label() }}</td>
                        <td class="px-4 py-3 text-gray-700">{{ $activity->summary }}</td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="4" class="px-4 py-8 text-center text-gray-500">{{ __('No activity recorded yet.') }}</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    {{ $activities->links() }}
</div>
