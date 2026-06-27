@php
    use App\Enums\AiGenerationType;
@endphp

@switch($generation->type)
    @case(AiGenerationType::Email)
    @case(AiGenerationType::FollowUp)
        @if (! empty($generation->output['subject']))
            <div>
                <div class="text-xs font-medium uppercase text-gray-500">{{ __('Subject') }}</div>
                <div class="mt-1 font-medium text-gray-900">{{ $generation->output['subject'] }}</div>
            </div>
        @endif
        @if (! empty($generation->output['body']))
            <div>
                <div class="text-xs font-medium uppercase text-gray-500">{{ __('Body') }}</div>
                <div class="mt-1 whitespace-pre-wrap">{{ $generation->output['body'] }}</div>
            </div>
        @endif
        @if (! empty($generation->output['call_to_action']))
            <div>
                <div class="text-xs font-medium uppercase text-gray-500">{{ __('Call to Action') }}</div>
                <div class="mt-1">{{ $generation->output['call_to_action'] }}</div>
            </div>
        @endif
        @break

    @case(AiGenerationType::Whatsapp)
        @if (! empty($generation->output['opening_line']))
            <div>
                <div class="text-xs font-medium uppercase text-gray-500">{{ __('Opening') }}</div>
                <div class="mt-1">{{ $generation->output['opening_line'] }}</div>
            </div>
        @endif
        @if (! empty($generation->output['message']))
            <div>
                <div class="text-xs font-medium uppercase text-gray-500">{{ __('Message') }}</div>
                <div class="mt-1 whitespace-pre-wrap">{{ $generation->output['message'] }}</div>
            </div>
        @endif
        @break

    @case(AiGenerationType::Translate)
        @if (! empty($generation->output['translated_text']))
            <div>
                <div class="text-xs font-medium uppercase text-gray-500">{{ __('Translation') }}</div>
                <div class="mt-1 whitespace-pre-wrap">{{ $generation->output['translated_text'] }}</div>
            </div>
        @endif
        @break

    @case(AiGenerationType::CompanySummary)
        @if (! empty($generation->output['summary']))
            <div>
                <div class="text-xs font-medium uppercase text-gray-500">{{ __('Summary') }}</div>
                <div class="mt-1 whitespace-pre-wrap">{{ $generation->output['summary'] }}</div>
            </div>
        @endif
        @if (! empty($generation->output['key_facts']))
            <div>
                <div class="text-xs font-medium uppercase text-gray-500">{{ __('Key Facts') }}</div>
                <ul class="mt-1 list-disc ps-5 space-y-1">
                    @foreach ($generation->output['key_facts'] as $fact)
                        <li>{{ $fact }}</li>
                    @endforeach
                </ul>
            </div>
        @endif
        @if (! empty($generation->output['suggested_angle']))
            <div>
                <div class="text-xs font-medium uppercase text-gray-500">{{ __('Suggested Angle') }}</div>
                <div class="mt-1">{{ $generation->output['suggested_angle'] }}</div>
            </div>
        @endif
        @break

    @case(AiGenerationType::NextBestAction)
        @if (! empty($generation->output['action']))
            <div>
                <div class="text-xs font-medium uppercase text-gray-500">{{ __('Recommended Action') }}</div>
                <div class="mt-1 font-medium text-gray-900">{{ $generation->output['action'] }}</div>
            </div>
        @endif
        @if (! empty($generation->output['rationale']))
            <div>
                <div class="text-xs font-medium uppercase text-gray-500">{{ __('Rationale') }}</div>
                <div class="mt-1">{{ $generation->output['rationale'] }}</div>
            </div>
        @endif
        @if (! empty($generation->output['priority']))
            <div>
                <div class="text-xs font-medium uppercase text-gray-500">{{ __('Priority') }}</div>
                <div class="mt-1 capitalize">{{ $generation->output['priority'] }}</div>
            </div>
        @endif
        @break

    @case(AiGenerationType::RiskAnalysis)
        @if (! empty($generation->output['risk_level']))
            <div>
                <div class="text-xs font-medium uppercase text-gray-500">{{ __('Risk Level') }}</div>
                <div class="mt-1 capitalize font-medium">{{ $generation->output['risk_level'] }}</div>
            </div>
        @endif
        @if (! empty($generation->output['overall_assessment']))
            <div>
                <div class="text-xs font-medium uppercase text-gray-500">{{ __('Assessment') }}</div>
                <div class="mt-1 whitespace-pre-wrap">{{ $generation->output['overall_assessment'] }}</div>
            </div>
        @endif
        @if (! empty($generation->output['risks']))
            <div>
                <div class="text-xs font-medium uppercase text-gray-500">{{ __('Risks') }}</div>
                <div class="mt-2 space-y-3">
                    @foreach ($generation->output['risks'] as $risk)
                        <div class="rounded-md bg-gray-50 p-3">
                            <div class="font-medium capitalize">{{ $risk['type'] ?? __('Risk') }}</div>
                            <div class="mt-1">{{ $risk['description'] ?? '' }}</div>
                            @if (! empty($risk['mitigation']))
                                <div class="mt-2 text-xs text-gray-600">{{ __('Mitigation') }}: {{ $risk['mitigation'] }}</div>
                            @endif
                        </div>
                    @endforeach
                </div>
            </div>
        @endif
        @break
@endswitch
