<div class="space-y-6">
    <div class="flex flex-wrap gap-2">
        <a href="{{ route('crm.companies.index') }}" wire:navigate class="text-sm text-indigo-600 hover:text-indigo-800">{{ __('Companies') }}</a>
        <span class="text-gray-300">|</span>
        <a href="{{ route('crm.pipeline') }}" wire:navigate class="text-sm text-indigo-600 hover:text-indigo-800">{{ __('Pipeline') }}</a>
    </div>

    <div class="grid gap-4 sm:grid-cols-2 lg:grid-cols-4">
        <div class="rounded-lg border border-gray-200 p-4">
            <div class="text-sm text-gray-500">{{ __('Total Companies') }}</div>
            <div class="mt-1 text-2xl font-semibold text-gray-900">{{ number_format($dashboard['companies_total']) }}</div>
        </div>
        <div class="rounded-lg border border-gray-200 p-4">
            <div class="text-sm text-gray-500">{{ __('Active Deals') }}</div>
            <div class="mt-1 text-2xl font-semibold text-gray-900">{{ number_format($dashboard['companies_with_deals']) }}</div>
        </div>
        <div class="rounded-lg border border-gray-200 p-4">
            <div class="text-sm text-gray-500">{{ __('Pipeline Value') }}</div>
            <div class="mt-1 text-2xl font-semibold text-gray-900">${{ number_format($dashboard['pipeline_value'], 0) }}</div>
        </div>
        <div class="rounded-lg border border-gray-200 p-4">
            <div class="text-sm text-gray-500">{{ __('Won Value') }}</div>
            <div class="mt-1 text-2xl font-semibold text-green-700">${{ number_format($dashboard['won_value'], 0) }}</div>
        </div>
        <div class="rounded-lg border border-gray-200 p-4">
            <div class="text-sm text-gray-500">{{ __('Open Tasks') }}</div>
            <div class="mt-1 text-2xl font-semibold text-gray-900">{{ number_format($dashboard['tasks_open']) }}</div>
        </div>
        <div class="rounded-lg border border-gray-200 p-4">
            <div class="text-sm text-gray-500">{{ __('Overdue Tasks') }}</div>
            <div class="mt-1 text-2xl font-semibold text-red-600">{{ number_format($dashboard['tasks_overdue']) }}</div>
        </div>
        <div class="rounded-lg border border-gray-200 p-4">
            <div class="text-sm text-gray-500">{{ __('Upcoming Meetings') }}</div>
            <div class="mt-1 text-2xl font-semibold text-gray-900">{{ number_format($dashboard['meetings_upcoming']) }}</div>
        </div>
        <div class="rounded-lg border border-gray-200 p-4">
            <div class="text-sm text-gray-500">{{ __('Activities This Week') }}</div>
            <div class="mt-1 text-2xl font-semibold text-gray-900">{{ number_format($dashboard['activities_this_week']) }}</div>
        </div>
    </div>

    <div class="grid gap-6 lg:grid-cols-2">
        <div class="rounded-lg border border-gray-200 p-4">
            <h3 class="text-sm font-medium text-gray-900">{{ __('Pipeline by Stage') }}</h3>
            <div class="mt-4 space-y-3">
                @foreach ($dashboard['pipeline_by_stage'] as $stage)
                    <div>
                        <div class="flex justify-between text-sm">
                            <span class="text-gray-700">{{ $stage['label'] }}</span>
                            <span class="text-gray-500">{{ $stage['deals_count'] }} · ${{ number_format($stage['value'], 0) }}</span>
                        </div>
                        <div class="mt-1 h-2 rounded-full bg-gray-100">
                            <div class="h-2 rounded-full bg-indigo-500" style="width: {{ $dashboard['companies_with_deals'] > 0 ? min(100, ($stage['deals_count'] / max($dashboard['companies_with_deals'], 1)) * 100) : 0 }}%"></div>
                        </div>
                    </div>
                @endforeach
            </div>
        </div>

        <div class="space-y-6">
            <div class="rounded-lg border border-gray-200 p-4">
                <h3 class="text-sm font-medium text-gray-900">{{ __('Companies by Country') }}</h3>
                <ul class="mt-4 space-y-2 text-sm">
                    @forelse ($dashboard['companies_by_country'] as $row)
                        <li class="flex justify-between text-gray-700">
                            <span>{{ $row['country_code'] }}</span>
                            <span class="font-medium">{{ $row['total'] }}</span>
                        </li>
                    @empty
                        <li class="text-gray-500">{{ __('No data yet.') }}</li>
                    @endforelse
                </ul>
            </div>

            <div class="rounded-lg border border-gray-200 p-4">
                <h3 class="text-sm font-medium text-gray-900">{{ __('Activity by Type (30 days)') }}</h3>
                <ul class="mt-4 space-y-2 text-sm">
                    @forelse ($dashboard['activity_by_type'] as $row)
                        <li class="flex justify-between text-gray-700">
                            <span>{{ is_object($row['activity_type']) ? $row['activity_type']->label() : ucfirst($row['activity_type']) }}</span>
                            <span class="font-medium">{{ $row['total'] }}</span>
                        </li>
                    @empty
                        <li class="text-gray-500">{{ __('No activities logged yet.') }}</li>
                    @endforelse
                </ul>
            </div>
        </div>
    </div>
</div>
