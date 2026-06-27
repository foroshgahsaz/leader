<div class="space-y-6">
    @if (session('status'))
        <div class="rounded-md bg-green-50 p-4 text-sm text-green-800">{{ session('status') }}</div>
    @endif

    <div class="flex flex-wrap items-center justify-between gap-3">
        <div class="flex flex-wrap gap-2">
            <a href="{{ route('discover.index') }}" wire:navigate class="text-sm text-indigo-600 hover:text-indigo-800">{{ __('Search') }}</a>
            <span class="text-gray-300">|</span>
            <a href="{{ route('discover.saved') }}" wire:navigate class="text-sm text-indigo-600 hover:text-indigo-800">{{ __('Saved Leads') }}</a>
        </div>
        @can('update', $buyer)
            <button type="button" wire:click="toggleFavorite" class="text-sm text-gray-600 hover:text-yellow-600">
                {{ $buyer->is_favorite ? __('★ Favorited') : __('☆ Add to favorites') }}
            </button>
        @endcan
    </div>

    <div class="grid gap-6 lg:grid-cols-3">
        <div class="lg:col-span-2 space-y-6">
            <div class="rounded-lg border border-gray-200 p-4">
                <div class="flex flex-wrap gap-4 text-sm text-gray-600">
                    <div><span class="font-medium text-gray-900">{{ __('Industry') }}:</span> {{ $buyer->industry ?? '—' }}</div>
                    <div><span class="font-medium text-gray-900">{{ __('Website') }}:</span>
                        @if ($buyer->website)
                            <a href="{{ $buyer->website }}" target="_blank" rel="noopener" class="text-indigo-600 hover:underline">{{ $buyer->website }}</a>
                        @else
                            —
                        @endif
                    </div>
                    <div><span class="font-medium text-gray-900">{{ __('Type') }}:</span> {{ $buyer->company_type?->label() ?? '—' }}</div>
                    <div><span class="font-medium text-gray-900">{{ __('Status') }}:</span> {{ $buyer->status->label() }}</div>
                </div>
            </div>

            <div class="border-b border-gray-200">
                <nav class="-mb-px flex flex-wrap gap-4">
                    @foreach (['overview' => __('Overview'), 'ai' => __('AI Assistant'), 'notes' => __('Notes'), 'tasks' => __('Tasks'), 'timeline' => __('Timeline'), 'history' => __('History')] as $tab => $label)
                        <button type="button" wire:click="setTab('{{ $tab }}')" @class([
                            'border-b-2 px-1 py-2 text-sm font-medium',
                            'border-indigo-500 text-indigo-600' => $activeTab === $tab,
                            'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300' => $activeTab !== $tab,
                        ])>{{ $label }}</button>
                    @endforeach
                </nav>
            </div>

            @if ($activeTab === 'overview')
                @if ($summary)
                    <div class="space-y-4">
                        <div>
                            <h3 class="text-sm font-medium text-gray-900">{{ __('AI Summary') }}</h3>
                            <p class="mt-2 text-sm text-gray-700">{{ $summary->summary }}</p>
                        </div>
                        @if ($summary->key_facts)
                            <div>
                                <h4 class="text-sm font-medium text-gray-900">{{ __('Key Facts') }}</h4>
                                <ul class="mt-2 list-disc ps-5 text-sm text-gray-700 space-y-1">
                                    @foreach ($summary->key_facts as $fact)
                                        <li>{{ $fact }}</li>
                                    @endforeach
                                </ul>
                            </div>
                        @endif
                        @if ($summary->suggested_angle)
                            <div>
                                <h4 class="text-sm font-medium text-gray-900">{{ __('Suggested Angle') }}</h4>
                                <p class="mt-2 text-sm text-gray-700">{{ $summary->suggested_angle }}</p>
                            </div>
                        @endif
                    </div>
                @else
                    <p class="text-sm text-gray-500">{{ __('No summary available.') }}</p>
                @endif
            @endif

            @if ($activeTab === 'ai')
                @can('viewAny', \App\Models\AiGeneration::class)
                    <livewire:ai-assistant.lead-ai-assistant :buyer="$buyer" :key="'ai-assistant-'.$buyer->id" />
                @else
                    <p class="text-sm text-gray-500">{{ __('You do not have permission to use AI features.') }}</p>
                @endcan
            @endif

            @if ($activeTab === 'notes')
                @can('update', $buyer)
                    <form wire:submit="addNote" class="space-y-3 rounded-lg border border-gray-200 p-4">
                        <div>
                            <x-input-label for="noteBody" :value="__('Add note')" />
                            <textarea wire:model="noteBody" id="noteBody" rows="3" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500"></textarea>
                            @error('noteBody') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
                        </div>
                        <label class="inline-flex items-center gap-2 text-sm text-gray-600">
                            <input type="checkbox" wire:model="notePinned" class="rounded border-gray-300 text-indigo-600 focus:ring-indigo-500">
                            {{ __('Pin note') }}
                        </label>
                        <x-primary-button type="submit">{{ __('Save Note') }}</x-primary-button>
                    </form>
                @endcan

                <div class="space-y-3">
                    @forelse ($buyer->notes as $note)
                        <div wire:key="note-{{ $note->id }}" class="rounded-lg border border-gray-200 p-4">
                            <div class="flex items-center justify-between text-xs text-gray-500">
                                <span>{{ $note->author?->fullName() ?? __('Unknown') }}</span>
                                <span>{{ $note->created_at->format('M j, Y g:i A') }}</span>
                            </div>
                            <p class="mt-2 text-sm text-gray-700 whitespace-pre-wrap">{{ $note->body }}</p>
                        </div>
                    @empty
                        <p class="text-sm text-gray-500">{{ __('No notes yet.') }}</p>
                    @endforelse
                </div>
            @endif

            @if ($activeTab === 'tasks')
                @can('update', $buyer)
                    <form wire:submit="createTask" class="space-y-3 rounded-lg border border-gray-200 p-4">
                        <div>
                            <x-input-label for="taskTitle" :value="__('Task title')" />
                            <x-text-input wire:model="taskTitle" id="taskTitle" class="mt-1 block w-full" />
                            @error('taskTitle') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
                        </div>
                        <div>
                            <x-input-label for="taskDescription" :value="__('Description')" />
                            <textarea wire:model="taskDescription" id="taskDescription" rows="2" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500"></textarea>
                        </div>
                        <div class="grid gap-3 md:grid-cols-2">
                            <div>
                                <x-input-label for="taskDueAt" :value="__('Due date')" />
                                <x-text-input wire:model="taskDueAt" id="taskDueAt" type="date" class="mt-1 block w-full" />
                            </div>
                            <div>
                                <x-input-label for="taskPriority" :value="__('Priority')" />
                                <select wire:model="taskPriority" id="taskPriority" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                                    @foreach ($priorities as $priority)
                                        <option value="{{ $priority->value }}">{{ $priority->label() }}</option>
                                    @endforeach
                                </select>
                            </div>
                        </div>
                        <x-primary-button type="submit">{{ __('Create Task') }}</x-primary-button>
                    </form>
                @endcan

                <div class="space-y-3">
                    @forelse ($buyer->tasks as $task)
                        <div wire:key="task-{{ $task->id }}" class="rounded-lg border border-gray-200 p-4">
                            <div class="flex items-center justify-between gap-2">
                                <h4 class="font-medium text-gray-900">{{ $task->title }}</h4>
                                <span class="text-xs rounded-full bg-gray-100 px-2 py-0.5 text-gray-700">{{ $task->status->label() ?? $task->status }}</span>
                            </div>
                            @if ($task->description)
                                <p class="mt-2 text-sm text-gray-600">{{ $task->description }}</p>
                            @endif
                            <div class="mt-2 text-xs text-gray-500">
                                {{ __('Due') }}: {{ $task->due_at?->format('M j, Y') }}
                                · {{ __('Assigned to') }}: {{ $task->assignee?->fullName() ?? '—' }}
                            </div>
                        </div>
                    @empty
                        <p class="text-sm text-gray-500">{{ __('No tasks yet.') }}</p>
                    @endforelse
                </div>
            @endif

            @if ($activeTab === 'timeline')
                <div class="space-y-3">
                    @forelse ($timeline as $event)
                        <div wire:key="timeline-{{ $event->id }}" class="rounded-lg border border-gray-200 p-4">
                            <div class="flex items-center justify-between text-xs text-gray-500">
                                <span>{{ $event->actor?->fullName() ?? __('System') }}</span>
                                <span>{{ $event->occurred_at->format('M j, Y g:i A') }}</span>
                            </div>
                            <p class="mt-2 text-sm text-gray-700">{{ $event->summary }}</p>
                        </div>
                    @empty
                        <p class="text-sm text-gray-500">{{ __('No activity yet.') }}</p>
                    @endforelse
                </div>
            @endif

            @if ($activeTab === 'history')
                <div class="overflow-hidden border border-gray-200 rounded-lg">
                    <table class="min-w-full divide-y divide-gray-200 text-sm">
                        <thead class="bg-gray-50">
                            <tr>
                                <th class="px-4 py-3 text-left font-medium text-gray-600">{{ __('When') }}</th>
                                <th class="px-4 py-3 text-left font-medium text-gray-600">{{ __('Field') }}</th>
                                <th class="px-4 py-3 text-left font-medium text-gray-600">{{ __('Change') }}</th>
                                <th class="px-4 py-3 text-left font-medium text-gray-600">{{ __('By') }}</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-200 bg-white">
                            @forelse ($history as $entry)
                                <tr wire:key="history-{{ $entry->id }}">
                                    <td class="px-4 py-3 text-gray-500">{{ $entry->changed_at->format('M j, Y g:i A') }}</td>
                                    <td class="px-4 py-3">{{ $entry->field_name }}</td>
                                    <td class="px-4 py-3 text-gray-700">{{ $entry->old_value }} → {{ $entry->new_value }}</td>
                                    <td class="px-4 py-3">{{ $entry->changedByUser?->fullName() ?? '—' }}</td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="4" class="px-4 py-8 text-center text-gray-500">{{ __('No field history recorded.') }}</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            @endif
        </div>

        <div class="space-y-6">
            @if ($buyer->currentScore)
                <div class="rounded-lg border border-gray-200 p-4">
                    <h3 class="text-sm font-medium text-gray-900">{{ __('Lead Score') }}</h3>
                    <div class="mt-2 text-3xl font-bold text-indigo-600">{{ $buyer->currentScore->score }}</div>
                    <p class="text-sm text-gray-600">{{ $buyer->currentScore->score_band->label() }}</p>
                    @if ($buyer->currentScore->explanation)
                        <p class="mt-2 text-xs text-gray-500">{{ $buyer->currentScore->explanation }}</p>
                    @endif
                </div>
            @endif

            @can('update', $buyer)
                <div class="rounded-lg border border-gray-200 p-4 space-y-3">
                    <h3 class="text-sm font-medium text-gray-900">{{ __('Update Status') }}</h3>
                    <form wire:submit="updateStatus" class="space-y-3">
                        <select wire:model="newStatus" class="block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                            @foreach ($statuses as $value => $label)
                                <option value="{{ $value }}">{{ $label }}</option>
                            @endforeach
                        </select>
                        <textarea wire:model="statusReason" rows="2" placeholder="{{ __('Reason (optional)') }}" class="block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500"></textarea>
                        <x-primary-button type="submit" class="w-full justify-center">{{ __('Update Status') }}</x-primary-button>
                    </form>
                </div>

                <div class="rounded-lg border border-gray-200 p-4 space-y-3">
                    <h3 class="text-sm font-medium text-gray-900">{{ __('Tags') }}</h3>
                    <div class="flex flex-wrap gap-2">
                        @foreach ($buyer->tags as $tag)
                            <span wire:key="tag-{{ $tag->id }}" class="inline-flex items-center gap-1 rounded bg-indigo-50 px-2 py-1 text-xs text-indigo-700">
                                {{ $tag->tag }}
                                <button type="button" wire:click="removeTag('{{ $tag->tag }}')" class="text-indigo-400 hover:text-indigo-800">×</button>
                            </span>
                        @endforeach
                    </div>
                    <form wire:submit="addTag" class="flex gap-2">
                        <x-text-input wire:model="newTag" class="block w-full" placeholder="{{ __('New tag') }}" />
                        <x-secondary-button type="submit">{{ __('Add') }}</x-secondary-button>
                    </form>
                </div>
            @endcan
        </div>
    </div>
</div>
