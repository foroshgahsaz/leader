@php
    $kpis = $data['kpis'];
    $performance = $data['performance'];
    $maxLeadTrend = max(1, collect($data['charts']['leads_trend'])->max('value'));
    $maxEmailTrend = max(1, collect($data['charts']['emails_trend'])->max('value'));
    $maxReplyTrend = max(1, collect($data['charts']['replies_trend'])->max('value'));
    $maxScoreCount = max(1, collect($data['charts']['score_distribution'])->max('count'));
    $maxPipelineDeals = max(1, $data['pipeline']->max('deals_count'));
@endphp

<div class="py-8">
    <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-8">
        <div class="flex flex-col gap-2 sm:flex-row sm:items-center sm:justify-between">
            <div>
                <h1 class="text-2xl font-bold text-gray-900">{{ __('Dashboard') }}</h1>
                <p class="text-sm text-gray-600">{{ __('Welcome back, :name', ['name' => auth()->user()->fullName()]) }} · {{ now()->format('l, F j, Y') }}</p>
            </div>
            <div class="flex flex-wrap gap-2">
                @can('leads.search')
                    <a href="{{ route('discover.index') }}" wire:navigate class="inline-flex items-center rounded-md bg-indigo-600 px-3 py-2 text-sm font-medium text-white hover:bg-indigo-700">{{ __('Find Leads') }}</a>
                @endcan
                @can('crm.view')
                    <a href="{{ route('crm.companies.index') }}" wire:navigate class="inline-flex items-center rounded-md border border-gray-300 bg-white px-3 py-2 text-sm font-medium text-gray-700 hover:bg-gray-50">{{ __('CRM') }}</a>
                @endcan
            </div>
        </div>

        {{-- KPI Cards --}}
        <div class="grid gap-4 sm:grid-cols-2 xl:grid-cols-3 2xl:grid-cols-6">
            <div class="rounded-lg border border-gray-200 bg-white p-4 shadow-sm">
                <div class="text-xs font-medium uppercase tracking-wide text-gray-500">{{ __("Today's Leads") }}</div>
                <div class="mt-2 text-3xl font-bold text-gray-900">{{ number_format($kpis['todays_leads']) }}</div>
                <div class="mt-1 text-xs text-gray-500">{{ __(':count this week', ['count' => $kpis['leads_this_week']]) }}</div>
            </div>
            <div class="rounded-lg border border-gray-200 bg-white p-4 shadow-sm">
                <div class="text-xs font-medium uppercase tracking-wide text-gray-500">{{ __('AI Scores') }}</div>
                <div class="mt-2 text-3xl font-bold text-indigo-600">{{ $kpis['avg_ai_score'] }}<span class="text-lg text-gray-400">/100</span></div>
                <div class="mt-1 text-xs text-gray-500">{{ __(':high high · :total scored', ['high' => $kpis['high_score_leads'], 'total' => $kpis['scored_leads']]) }}</div>
            </div>
            <div class="rounded-lg border border-gray-200 bg-white p-4 shadow-sm">
                <div class="text-xs font-medium uppercase tracking-wide text-gray-500">{{ __('Emails Sent') }}</div>
                <div class="mt-2 text-3xl font-bold text-gray-900">{{ number_format($kpis['emails_sent_today']) }}</div>
                <div class="mt-1 text-xs text-gray-500">{{ __(':count this week', ['count' => $kpis['emails_sent_week']]) }}</div>
            </div>
            <div class="rounded-lg border border-gray-200 bg-white p-4 shadow-sm">
                <div class="text-xs font-medium uppercase tracking-wide text-gray-500">{{ __('Replies') }}</div>
                <div class="mt-2 text-3xl font-bold text-green-600">{{ number_format($kpis['replies_today']) }}</div>
                <div class="mt-1 text-xs text-gray-500">{{ __(':total total replied leads', ['total' => $kpis['total_replied_leads']]) }}</div>
            </div>
            <div class="rounded-lg border border-gray-200 bg-white p-4 shadow-sm">
                <div class="text-xs font-medium uppercase tracking-wide text-gray-500">{{ __('Tasks') }}</div>
                <div class="mt-2 text-3xl font-bold text-gray-900">{{ number_format($kpis['tasks_due_today']) }}</div>
                <div class="mt-1 text-xs text-gray-500">{{ __(':overdue overdue · :open open', ['overdue' => $kpis['tasks_overdue'], 'open' => $kpis['open_tasks']]) }}</div>
            </div>
            <div class="rounded-lg border border-gray-200 bg-white p-4 shadow-sm">
                <div class="text-xs font-medium uppercase tracking-wide text-gray-500">{{ __('Pipeline') }}</div>
                <div class="mt-2 text-3xl font-bold text-gray-900">${{ number_format($kpis['pipeline_value'], 0) }}</div>
                <div class="mt-1 text-xs text-gray-500">{{ __(':deals open deals', ['deals' => $kpis['open_deals']]) }}</div>
            </div>
        </div>

        {{-- Charts --}}
        <div class="grid gap-6 lg:grid-cols-2">
            <div class="rounded-lg border border-gray-200 bg-white p-5 shadow-sm">
                <h2 class="text-sm font-semibold text-gray-900">{{ __('Leads (14 days)') }}</h2>
                <div class="mt-4 flex items-end gap-1 h-40">
                    @foreach ($data['charts']['leads_trend'] as $point)
                        <div class="flex flex-1 flex-col items-center gap-1">
                            <div class="w-full rounded-t bg-indigo-500" style="height: {{ max(4, ($point['value'] / $maxLeadTrend) * 100) }}%" title="{{ $point['value'] }}"></div>
                            <span class="text-[10px] text-gray-400 rotate-0 truncate w-full text-center">{{ $point['label'] }}</span>
                        </div>
                    @endforeach
                </div>
            </div>

            <div class="rounded-lg border border-gray-200 bg-white p-5 shadow-sm">
                <h2 class="text-sm font-semibold text-gray-900">{{ __('Emails vs Replies (14 days)') }}</h2>
                <div class="mt-4 flex items-end gap-2 h-40">
                    @foreach ($data['charts']['emails_trend'] as $index => $emailPoint)
                        @php $replyPoint = $data['charts']['replies_trend'][$index]; @endphp
                        <div class="flex flex-1 flex-col items-center gap-1">
                            <div class="flex w-full items-end justify-center gap-0.5 h-32">
                                <div class="w-2/5 rounded-t bg-blue-500" style="height: {{ max(4, ($emailPoint['value'] / $maxEmailTrend) * 100) }}%" title="Emails: {{ $emailPoint['value'] }}"></div>
                                <div class="w-2/5 rounded-t bg-green-500" style="height: {{ max(4, ($replyPoint['value'] / $maxReplyTrend) * 100) }}%" title="Replies: {{ $replyPoint['value'] }}"></div>
                            </div>
                            <span class="text-[10px] text-gray-400">{{ $emailPoint['label'] }}</span>
                        </div>
                    @endforeach
                </div>
                <div class="mt-2 flex gap-4 text-xs text-gray-500">
                    <span class="inline-flex items-center gap-1"><span class="h-2 w-2 rounded bg-blue-500"></span>{{ __('Emails') }}</span>
                    <span class="inline-flex items-center gap-1"><span class="h-2 w-2 rounded bg-green-500"></span>{{ __('Replies') }}</span>
                </div>
            </div>

            <div class="rounded-lg border border-gray-200 bg-white p-5 shadow-sm">
                <h2 class="text-sm font-semibold text-gray-900">{{ __('AI Score Distribution') }}</h2>
                <div class="mt-4 space-y-3">
                    @foreach ($data['charts']['score_distribution'] as $band)
                        <div>
                            <div class="flex justify-between text-sm">
                                <span class="text-gray-700">{{ $band['label'] }}</span>
                                <span class="font-medium text-gray-900">{{ $band['count'] }}</span>
                            </div>
                            <div class="mt-1 h-2 rounded-full bg-gray-100">
                                <div @class([
                                    'h-2 rounded-full',
                                    'bg-green-500' => $band['band'] === 'high',
                                    'bg-yellow-500' => $band['band'] === 'medium',
                                    'bg-gray-400' => $band['band'] === 'low',
                                ]) style="width: {{ ($band['count'] / $maxScoreCount) * 100 }}%"></div>
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>

            <div class="rounded-lg border border-gray-200 bg-white p-5 shadow-sm">
                <h2 class="text-sm font-semibold text-gray-900">{{ __('Pipeline Funnel') }}</h2>
                <div class="mt-4 space-y-3">
                    @foreach ($data['pipeline'] as $stage)
                        <div>
                            <div class="flex justify-between text-sm">
                                <span class="text-gray-700">{{ $stage['label'] }}</span>
                                <span class="text-gray-500">{{ $stage['deals_count'] }} · ${{ number_format($stage['value'], 0) }}</span>
                            </div>
                            <div class="mt-1 h-2 rounded-full bg-gray-100">
                                <div @class([
                                    'h-2 rounded-full',
                                    'bg-green-500' => $stage['is_won'],
                                    'bg-red-400' => $stage['is_closed'] && ! $stage['is_won'],
                                    'bg-indigo-500' => ! $stage['is_closed'],
                                ]) style="width: {{ ($stage['deals_count'] / $maxPipelineDeals) * 100 }}%"></div>
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>
        </div>

        {{-- Performance --}}
        <div class="rounded-lg border border-gray-200 bg-white p-5 shadow-sm">
            <h2 class="text-sm font-semibold text-gray-900">{{ __('Performance') }}</h2>
            <div class="mt-4 grid gap-4 sm:grid-cols-2 lg:grid-cols-4">
                <div class="rounded-md bg-gray-50 p-4">
                    <div class="text-xs uppercase text-gray-500">{{ __('Reply Rate') }}</div>
                    <div class="mt-1 text-2xl font-bold text-gray-900">{{ $performance['reply_rate'] }}%</div>
                </div>
                <div class="rounded-md bg-gray-50 p-4">
                    <div class="text-xs uppercase text-gray-500">{{ __('Win Rate') }}</div>
                    <div class="mt-1 text-2xl font-bold text-gray-900">{{ $performance['win_rate'] }}%</div>
                    <div class="text-xs text-gray-500">{{ __(':won won · :lost lost', ['won' => $performance['won_deals'], 'lost' => $performance['lost_deals']]) }}</div>
                </div>
                <div class="rounded-md bg-gray-50 p-4">
                    <div class="text-xs uppercase text-gray-500">{{ __('Qualification Rate') }}</div>
                    <div class="mt-1 text-2xl font-bold text-gray-900">{{ $performance['qualification_rate'] }}%</div>
                </div>
                <div class="rounded-md bg-gray-50 p-4">
                    <div class="text-xs uppercase text-gray-500">{{ __('Avg Deal Value') }}</div>
                    <div class="mt-1 text-2xl font-bold text-gray-900">${{ number_format($performance['avg_deal_value'], 0) }}</div>
                </div>
            </div>
            <div class="mt-4 grid gap-4 sm:grid-cols-3 text-sm">
                <div class="flex items-center justify-between rounded-md border border-gray-200 px-4 py-3">
                    <span class="text-gray-600">{{ __('Leads vs last week') }}</span>
                    <span @class(['font-semibold', 'text-green-600' => $performance['leads_week_change'] >= 0, 'text-red-600' => $performance['leads_week_change'] < 0])>
                        {{ $performance['leads_week_change'] >= 0 ? '+' : '' }}{{ $performance['leads_week_change'] }}%
                    </span>
                </div>
                <div class="flex items-center justify-between rounded-md border border-gray-200 px-4 py-3">
                    <span class="text-gray-600">{{ __('Emails vs last week') }}</span>
                    <span @class(['font-semibold', 'text-green-600' => $performance['emails_week_change'] >= 0, 'text-red-600' => $performance['emails_week_change'] < 0])>
                        {{ $performance['emails_week_change'] >= 0 ? '+' : '' }}{{ $performance['emails_week_change'] }}%
                    </span>
                </div>
                <div class="flex items-center justify-between rounded-md border border-gray-200 px-4 py-3">
                    <span class="text-gray-600">{{ __('Activities vs last week') }}</span>
                    <span @class(['font-semibold', 'text-green-600' => $performance['activities_week_change'] >= 0, 'text-red-600' => $performance['activities_week_change'] < 0])>
                        {{ $performance['activities_week_change'] >= 0 ? '+' : '' }}{{ $performance['activities_week_change'] }}%
                    </span>
                </div>
            </div>
        </div>

        {{-- Lists --}}
        <div class="grid gap-6 lg:grid-cols-3">
            <div class="rounded-lg border border-gray-200 bg-white p-5 shadow-sm lg:col-span-1">
                <div class="flex items-center justify-between">
                    <h2 class="text-sm font-semibold text-gray-900">{{ __("Today's Leads") }}</h2>
                    @can('crm.companies.view')
                        <a href="{{ route('crm.companies.index') }}" wire:navigate class="text-xs text-indigo-600 hover:text-indigo-800">{{ __('View all') }}</a>
                    @endcan
                </div>
                <ul class="mt-4 divide-y divide-gray-100">
                    @forelse ($data['todays_leads'] as $lead)
                        <li class="py-3">
                            @php
                                $leadUrl = auth()->user()->can('crm.companies.view')
                                    ? route('crm.companies.show', $lead)
                                    : route('discover.leads.show', $lead);
                            @endphp
                            <a href="{{ $leadUrl }}" wire:navigate class="block hover:bg-gray-50 -mx-2 px-2 rounded">
                                <div class="font-medium text-sm text-gray-900">{{ $lead->name }}</div>
                                <div class="text-xs text-gray-500">{{ $lead->country_code }} · Score: {{ $lead->currentScore?->score ?? '—' }}</div>
                            </a>
                        </li>
                    @empty
                        <li class="py-6 text-sm text-gray-500 text-center">{{ __('No leads saved today.') }}</li>
                    @endforelse
                </ul>
            </div>

            <div class="rounded-lg border border-gray-200 bg-white p-5 shadow-sm lg:col-span-1">
                <h2 class="text-sm font-semibold text-gray-900">{{ __('Top AI Scores') }}</h2>
                <ul class="mt-4 divide-y divide-gray-100">
                    @forelse ($data['top_scored_leads'] as $lead)
                        <li class="py-3 flex items-center justify-between gap-2">
                            @php
                                $leadUrl = auth()->user()->can('crm.companies.view')
                                    ? route('crm.companies.show', $lead)
                                    : route('discover.leads.show', $lead);
                            @endphp
                            <a href="{{ $leadUrl }}" wire:navigate class="text-sm font-medium text-indigo-600 hover:text-indigo-800 truncate">{{ $lead->name }}</a>
                            <span @class([
                                'shrink-0 rounded-full px-2 py-0.5 text-xs font-medium',
                                'bg-green-100 text-green-800' => $lead->currentScore?->score_band?->value === 'high',
                                'bg-yellow-100 text-yellow-800' => $lead->currentScore?->score_band?->value === 'medium',
                                'bg-gray-100 text-gray-800' => $lead->currentScore?->score_band?->value === 'low',
                            ])>{{ $lead->currentScore?->score }}</span>
                        </li>
                    @empty
                        <li class="py-6 text-sm text-gray-500 text-center">{{ __('No scored leads yet.') }}</li>
                    @endforelse
                </ul>
            </div>

            <div class="rounded-lg border border-gray-200 bg-white p-5 shadow-sm lg:col-span-1">
                <div class="flex items-center justify-between">
                    <h2 class="text-sm font-semibold text-gray-900">{{ __('Tasks Due Today') }}</h2>
                    @can('crm.tasks.view')
                        <a href="{{ route('crm.tasks') }}" wire:navigate class="text-xs text-indigo-600 hover:text-indigo-800">{{ __('View all') }}</a>
                    @endcan
                </div>
                <ul class="mt-4 divide-y divide-gray-100">
                    @forelse ($data['tasks_due_today'] as $task)
                        <li class="py-3">
                            <div class="text-sm font-medium text-gray-900">{{ $task->title }}</div>
                            <div class="text-xs text-gray-500">
                                {{ $task->due_at->format('g:i A') }}
                                @if ($task->buyer)
                                    · <a href="{{ route('crm.companies.show', $task->buyer) }}" wire:navigate class="text-indigo-600">{{ $task->buyer->name }}</a>
                                @endif
                            </div>
                        </li>
                    @empty
                        <li class="py-6 text-sm text-gray-500 text-center">{{ __('No tasks due today.') }}</li>
                    @endforelse
                </ul>
            </div>
        </div>
    </div>
</div>
