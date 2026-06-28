<div class="space-y-6" x-data="{ draggingDealId: null }">
    @if (session('status'))
        <div class="rounded-md bg-green-50 p-4 text-sm text-green-800">{{ session('status') }}</div>
    @endif

    <div class="flex flex-wrap gap-2">
        <a href="{{ route('crm.companies.index') }}" wire:navigate class="text-sm text-indigo-600 hover:text-indigo-800">{{ __('Companies') }}</a>
        <span class="text-gray-300">|</span>
        <a href="{{ route('crm.tasks') }}" wire:navigate class="text-sm text-indigo-600 hover:text-indigo-800">{{ __('Tasks') }}</a>
    </div>

    <div class="flex gap-4 overflow-x-auto pb-4">
        @foreach ($stages as $stage)
            <div
                class="min-w-[280px] flex-shrink-0 rounded-lg border border-gray-200 bg-gray-50"
                x-on:dragover.prevent
                x-on:drop.prevent="$wire.changeStage(draggingDealId, '{{ $stage->id }}')"
            >
                <div class="border-b border-gray-200 px-4 py-3">
                    <h3 class="text-sm font-semibold text-gray-900">{{ $stage->localizedLabel() }}</h3>
                    <p class="text-xs text-gray-500">{{ $stage->deals->count() }} {{ __('deals') }}</p>
                </div>
                <div class="space-y-3 p-3 min-h-[200px]">
                    @foreach ($stage->deals as $deal)
                        <div
                            wire:key="deal-{{ $deal->id }}"
                            draggable="true"
                            x-on:dragstart="draggingDealId = '{{ $deal->id }}'"
                            class="cursor-grab rounded-lg border border-gray-200 bg-white p-3 shadow-sm active:cursor-grabbing"
                        >
                            <div class="font-medium text-sm text-gray-900">{{ $deal->title }}</div>
                            <div class="mt-1 text-xs text-indigo-600">
                                <a href="{{ route('crm.companies.show', $deal->buyer) }}" wire:navigate>{{ $deal->buyer?->name }}</a>
                            </div>
                            @if ($deal->estimated_value)
                                <div class="mt-2 text-xs text-gray-600">
                                    {{ number_format((float) $deal->estimated_value, 0) }} {{ $deal->currency_code }}
                                </div>
                            @endif
                            @if ($deal->owner)
                                <div class="mt-1 text-xs text-gray-500">{{ $deal->owner->fullName() }}</div>
                            @endif
                            @can('changeStage', $deal)
                                <select
                                    wire:change="changeStage('{{ $deal->id }}', $event.target.value)"
                                    class="mt-2 block w-full rounded-md border-gray-300 text-xs shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                                >
                                    @foreach ($stages as $moveStage)
                                        <option value="{{ $moveStage->id }}" @selected($moveStage->id === $deal->stage_id)>{{ $moveStage->localizedLabel() }}</option>
                                    @endforeach
                                </select>
                            @endcan
                        </div>
                    @endforeach
                </div>
            </div>
        @endforeach
    </div>
</div>
