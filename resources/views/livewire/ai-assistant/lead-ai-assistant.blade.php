<div class="space-y-6" @if($hasPending) wire:poll.3s="refreshGenerations" @endif>
    @if (session('ai_status'))
        <div class="rounded-md bg-green-50 p-4 text-sm text-green-800">{{ session('ai_status') }}</div>
    @endif
    @if (session('ai_error'))
        <div class="rounded-md bg-red-50 p-4 text-sm text-red-800">{{ session('ai_error') }}</div>
    @endif

    @can('generate', \App\Models\AiGeneration::class)
        <div class="rounded-lg border border-gray-200 p-4 space-y-4">
            <h3 class="text-sm font-medium text-gray-900">{{ __('Generation Options') }}</h3>

            <div class="grid gap-4 md:grid-cols-3">
                <div>
                    <x-input-label for="tone" :value="__('Tone')" />
                    <select wire:model="tone" id="tone" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                        <option value="professional">{{ __('Professional') }}</option>
                        <option value="friendly">{{ __('Friendly') }}</option>
                        <option value="direct">{{ __('Direct') }}</option>
                        <option value="consultative">{{ __('Consultative') }}</option>
                    </select>
                </div>
                <div>
                    <x-input-label for="language" :value="__('Output Language')" />
                    <select wire:model="language" id="language" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                        <option value="en">English</option>
                        <option value="es">Spanish</option>
                        <option value="de">German</option>
                        <option value="fr">French</option>
                        <option value="tr">Turkish</option>
                        <option value="ar">Arabic</option>
                    </select>
                </div>
                <div class="md:col-span-1">
                    <x-input-label for="additionalInstructions" :value="__('Extra Instructions')" />
                    <x-text-input wire:model="additionalInstructions" id="additionalInstructions" class="mt-1 block w-full" placeholder="{{ __('Optional guidance for AI') }}" />
                </div>
            </div>

            <div class="flex flex-wrap gap-2">
                <x-primary-button type="button" wire:click="generateEmail">{{ __('Generate Email') }}</x-primary-button>
                <x-secondary-button type="button" wire:click="generateWhatsapp">{{ __('Generate WhatsApp') }}</x-secondary-button>
                <x-secondary-button type="button" wire:click="summarizeCompany">{{ __('Summarize Company') }}</x-secondary-button>
                <x-secondary-button type="button" wire:click="nextBestAction">{{ __('Next Best Action') }}</x-secondary-button>
                <x-secondary-button type="button" wire:click="riskAnalysis">{{ __('Risk Analysis') }}</x-secondary-button>
            </div>

            <div class="border-t border-gray-200 pt-4 space-y-3">
                <h4 class="text-sm font-medium text-gray-900">{{ __('Follow-up') }}</h4>
                <div class="grid gap-3 md:grid-cols-2">
                    <div>
                        <x-input-label for="followUpParentId" :value="__('Based on email')" />
                        <select wire:model="followUpParentId" id="followUpParentId" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                            <option value="">{{ __('Select a completed email') }}</option>
                            @foreach ($emailGenerations as $emailGeneration)
                                <option value="{{ $emailGeneration->id }}">{{ $emailGeneration->output['subject'] ?? $emailGeneration->id }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="flex items-end">
                        <x-secondary-button type="button" wire:click="generateFollowUp">{{ __('Generate Follow-up') }}</x-secondary-button>
                    </div>
                </div>
            </div>

            <div class="border-t border-gray-200 pt-4 space-y-3">
                <h4 class="text-sm font-medium text-gray-900">{{ __('Translate') }}</h4>
                <textarea wire:model="sourceText" rows="3" class="block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500" placeholder="{{ __('Paste text to translate...') }}"></textarea>
                <div class="flex flex-wrap items-end gap-3">
                    <div>
                        <x-input-label for="targetLanguage" :value="__('Target language')" />
                        <select wire:model="targetLanguage" id="targetLanguage" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                            <option value="es">Spanish</option>
                            <option value="de">German</option>
                            <option value="fr">French</option>
                            <option value="tr">Turkish</option>
                            <option value="ar">Arabic</option>
                            <option value="en">English</option>
                        </select>
                    </div>
                    <x-secondary-button type="button" wire:click="translate">{{ __('Translate') }}</x-secondary-button>
                </div>
            </div>
        </div>
    @endcan

    <div class="grid gap-6 lg:grid-cols-3">
        <div class="lg:col-span-1 space-y-2">
            <h3 class="text-sm font-medium text-gray-900">{{ __('Generation History') }}</h3>
            @forelse ($generations as $generation)
                <button
                    type="button"
                    wire:click="selectGeneration('{{ $generation->id }}')"
                    wire:key="ai-gen-{{ $generation->id }}"
                    @class([
                        'w-full rounded-lg border p-3 text-left text-sm',
                        'border-indigo-500 bg-indigo-50' => $selected?->id === $generation->id,
                        'border-gray-200 hover:bg-gray-50' => $selected?->id !== $generation->id,
                    ])
                >
                    <div class="font-medium text-gray-900">{{ $generation->type->label() }}</div>
                    <div class="mt-1 flex items-center justify-between text-xs text-gray-500">
                        <span>{{ $generation->created_at->format('M j, g:i A') }}</span>
                        <span @class([
                            'rounded-full px-2 py-0.5',
                            'bg-yellow-100 text-yellow-800' => in_array($generation->status->value, ['pending', 'processing']),
                            'bg-green-100 text-green-800' => $generation->status->value === 'completed',
                            'bg-red-100 text-red-800' => $generation->status->value === 'failed',
                        ])>{{ $generation->status->label() }}</span>
                    </div>
                </button>
            @empty
                <p class="text-sm text-gray-500">{{ __('No AI generations yet.') }}</p>
            @endforelse
        </div>

        <div class="lg:col-span-2 rounded-lg border border-gray-200 p-4">
            @if ($selected)
                <div class="flex items-center justify-between gap-2">
                    <h3 class="text-sm font-medium text-gray-900">{{ $selected->type->label() }}</h3>
                    @if ($selected->total_tokens)
                        <span class="text-xs text-gray-500">{{ __('Tokens') }}: {{ $selected->total_tokens }}</span>
                    @endif
                </div>

                @if ($selected->status->value === 'failed')
                    <p class="mt-4 text-sm text-red-600">{{ $selected->error_message }}</p>
                @elseif (in_array($selected->status->value, ['pending', 'processing']))
                    <p class="mt-4 text-sm text-gray-500">{{ __('Generating... This page will refresh automatically.') }}</p>
                @elseif ($selected->output)
                    <div class="mt-4 space-y-4 text-sm text-gray-700">
                        @include('livewire.ai-assistant.partials.generation-output', ['generation' => $selected])
                    </div>
                @endif
            @else
                <p class="text-sm text-gray-500">{{ __('Select a generation to view results.') }}</p>
            @endif
        </div>
    </div>
</div>
