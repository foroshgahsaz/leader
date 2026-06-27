<div class="space-y-6">
    @if (session('status'))
        <div class="rounded-md bg-green-50 p-4 text-sm text-green-800">{{ session('status') }}</div>
    @endif
    @if (session('error'))
        <div class="rounded-md bg-red-50 p-4 text-sm text-red-800">{{ session('error') }}</div>
    @endif

    <div class="flex flex-wrap gap-2">
        <a href="{{ route('crm.companies.index') }}" wire:navigate class="text-sm text-indigo-600 hover:text-indigo-800">{{ __('← Back to companies') }}</a>
    </div>

    <div class="rounded-lg border border-gray-200 p-4">
        <div class="flex flex-wrap gap-4 text-sm text-gray-600">
            <div><span class="font-medium text-gray-900">{{ __('Owner') }}:</span> {{ $company->owner?->fullName() ?? '—' }}</div>
            <div><span class="font-medium text-gray-900">{{ __('Stage') }}:</span> {{ $company->deal?->stage?->label ?? ucfirst($company->pipeline_stage ?? 'new') }}</div>
            <div><span class="font-medium text-gray-900">{{ __('Status') }}:</span> {{ $company->status->label() }}</div>
            @if ($company->website)
                <div><span class="font-medium text-gray-900">{{ __('Website') }}:</span>
                    <a href="{{ $company->website }}" target="_blank" rel="noopener" class="text-indigo-600 hover:underline">{{ $company->website }}</a>
                </div>
            @endif
        </div>
    </div>

    <div class="border-b border-gray-200">
        <nav class="-mb-px flex flex-wrap gap-4">
            @foreach ([
                'overview' => __('Overview'),
                'contacts' => __('Contacts'),
                'activities' => __('Activities'),
                'tasks' => __('Tasks'),
                'meetings' => __('Meetings'),
                'files' => __('Files'),
                'notes' => __('Notes'),
                'timeline' => __('Timeline'),
                'deal' => __('Deal'),
            ] as $tab => $label)
                <button type="button" wire:click="setTab('{{ $tab }}')" @class([
                    'border-b-2 px-1 py-2 text-sm font-medium',
                    'border-indigo-500 text-indigo-600' => $activeTab === $tab,
                    'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300' => $activeTab !== $tab,
                ])>{{ $label }}</button>
            @endforeach
        </nav>
    </div>

    @if ($activeTab === 'overview')
        @can('update', $company)
            <form wire:submit="updateCompany" class="space-y-4 rounded-lg border border-gray-200 p-4">
                <div class="grid gap-4 md:grid-cols-2">
                    <div>
                        <x-input-label for="editName" :value="__('Name')" />
                        <x-text-input wire:model="editName" id="editName" class="mt-1 block w-full" />
                        @error('editName') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
                    </div>
                    <div>
                        <x-input-label for="editCountryCode" :value="__('Country Code')" />
                        <x-text-input wire:model="editCountryCode" id="editCountryCode" class="mt-1 block w-full" maxlength="2" />
                    </div>
                    <div>
                        <x-input-label for="editCity" :value="__('City')" />
                        <x-text-input wire:model="editCity" id="editCity" class="mt-1 block w-full" />
                    </div>
                    <div>
                        <x-input-label for="editOwnerId" :value="__('Owner')" />
                        <select wire:model="editOwnerId" id="editOwnerId" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                            <option value="">{{ __('Unassigned') }}</option>
                            @foreach ($owners as $owner)
                                <option value="{{ $owner->id }}">{{ $owner->fullName() }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <x-input-label for="editIndustry" :value="__('Industry')" />
                        <x-text-input wire:model="editIndustry" id="editIndustry" class="mt-1 block w-full" />
                    </div>
                    <div>
                        <x-input-label for="editPhone" :value="__('Phone')" />
                        <x-text-input wire:model="editPhone" id="editPhone" class="mt-1 block w-full" />
                    </div>
                    <div class="md:col-span-2">
                        <x-input-label for="editWebsite" :value="__('Website')" />
                        <x-text-input wire:model="editWebsite" id="editWebsite" type="url" class="mt-1 block w-full" />
                    </div>
                    <div class="md:col-span-2">
                        <x-input-label for="editDescription" :value="__('Description')" />
                        <textarea wire:model="editDescription" id="editDescription" rows="4" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500"></textarea>
                    </div>
                </div>
                <x-primary-button type="submit">{{ __('Save Changes') }}</x-primary-button>
            </form>
        @else
            <div class="text-sm text-gray-700 space-y-2">
                <p><span class="font-medium">{{ __('Industry') }}:</span> {{ $company->industry ?? '—' }}</p>
                <p><span class="font-medium">{{ __('Phone') }}:</span> {{ $company->phone ?? '—' }}</p>
                <p><span class="font-medium">{{ __('Description') }}:</span> {{ $company->description ?? '—' }}</p>
            </div>
        @endcan
    @endif

    @if ($activeTab === 'contacts')
        @can('create', \App\Models\BuyerContact::class)
            <form wire:submit="createContact" class="space-y-3 rounded-lg border border-gray-200 p-4">
                <h3 class="text-sm font-medium text-gray-900">{{ __('Add Contact') }}</h3>
                <div class="grid gap-4 md:grid-cols-2">
                    <div>
                        <x-input-label for="contactFullName" :value="__('Full Name')" />
                        <x-text-input wire:model="contactFullName" id="contactFullName" class="mt-1 block w-full" />
                        @error('contactFullName') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
                    </div>
                    <div>
                        <x-input-label for="contactTitle" :value="__('Title')" />
                        <x-text-input wire:model="contactTitle" id="contactTitle" class="mt-1 block w-full" />
                    </div>
                    <div>
                        <x-input-label for="contactEmail" :value="__('Email')" />
                        <x-text-input wire:model="contactEmail" id="contactEmail" type="email" class="mt-1 block w-full" />
                    </div>
                    <div>
                        <x-input-label for="contactPhone" :value="__('Phone')" />
                        <x-text-input wire:model="contactPhone" id="contactPhone" class="mt-1 block w-full" />
                    </div>
                </div>
                <label class="inline-flex items-center gap-2 text-sm text-gray-600">
                    <input type="checkbox" wire:model="contactIsPrimary" class="rounded border-gray-300 text-indigo-600 focus:ring-indigo-500">
                    {{ __('Primary contact') }}
                </label>
                <x-primary-button type="submit">{{ __('Add Contact') }}</x-primary-button>
            </form>
        @endcan

        <div class="space-y-3">
            @forelse ($company->contacts as $contact)
                <div wire:key="contact-{{ $contact->id }}" class="rounded-lg border border-gray-200 p-4">
                    @if ($editingContactId === $contact->id)
                        <form wire:submit="updateContact" class="space-y-3">
                            <div class="grid gap-4 md:grid-cols-2">
                                <x-text-input wire:model="editContactFullName" class="block w-full" />
                                <x-text-input wire:model="editContactTitle" class="block w-full" placeholder="{{ __('Title') }}" />
                                <x-text-input wire:model="editContactEmail" type="email" class="block w-full" />
                                <x-text-input wire:model="editContactPhone" class="block w-full" />
                            </div>
                            <label class="inline-flex items-center gap-2 text-sm text-gray-600">
                                <input type="checkbox" wire:model="editContactIsPrimary" class="rounded border-gray-300 text-indigo-600 focus:ring-indigo-500">
                                {{ __('Primary contact') }}
                            </label>
                            <div class="flex gap-2">
                                <x-primary-button type="submit">{{ __('Save') }}</x-primary-button>
                                <x-secondary-button type="button" wire:click="$set('editingContactId', null)">{{ __('Cancel') }}</x-secondary-button>
                            </div>
                        </form>
                    @else
                        <div class="flex items-start justify-between gap-3">
                            <div>
                                <div class="font-medium text-gray-900">
                                    {{ $contact->full_name }}
                                    @if ($contact->is_primary)
                                        <span class="ms-2 inline-flex rounded-full bg-indigo-100 px-2 py-0.5 text-xs text-indigo-800">{{ __('Primary') }}</span>
                                    @endif
                                </div>
                                <div class="mt-1 text-sm text-gray-600">{{ $contact->title ?? '' }}</div>
                                <div class="mt-1 text-sm text-gray-600">{{ $contact->email ?? '' }} {{ $contact->phone ? '· '.$contact->phone : '' }}</div>
                            </div>
                            <div class="flex gap-2">
                                @can('update', $contact)
                                    <button type="button" wire:click="startEditContact('{{ $contact->id }}')" class="text-sm text-indigo-600 hover:text-indigo-800">{{ __('Edit') }}</button>
                                @endcan
                                @can('delete', $contact)
                                    <button type="button" wire:click="deleteContact('{{ $contact->id }}')" wire:confirm="{{ __('Delete this contact?') }}" class="text-sm text-red-600 hover:text-red-800">{{ __('Delete') }}</button>
                                @endcan
                            </div>
                        </div>
                    @endif
                </div>
            @empty
                <p class="text-sm text-gray-500">{{ __('No contacts yet.') }}</p>
            @endforelse
        </div>
    @endif

    @if ($activeTab === 'activities')
        @can('create', \App\Models\CrmActivity::class)
            <form wire:submit="logActivity" class="space-y-3 rounded-lg border border-gray-200 p-4">
                <div class="grid gap-4 md:grid-cols-2">
                    <div>
                        <x-input-label for="activityType" :value="__('Type')" />
                        <select wire:model="activityType" id="activityType" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                            @foreach ($activityTypes as $value => $label)
                                <option value="{{ $value }}">{{ $label }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <x-input-label for="activityDuration" :value="__('Duration (minutes)')" />
                        <x-text-input wire:model="activityDuration" id="activityDuration" type="number" min="1" class="mt-1 block w-full" />
                    </div>
                    <div class="md:col-span-2">
                        <x-input-label for="activitySubject" :value="__('Subject')" />
                        <x-text-input wire:model="activitySubject" id="activitySubject" class="mt-1 block w-full" />
                        @error('activitySubject') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
                    </div>
                    <div class="md:col-span-2">
                        <x-input-label for="activityBody" :value="__('Notes')" />
                        <textarea wire:model="activityBody" id="activityBody" rows="3" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500"></textarea>
                    </div>
                </div>
                <x-primary-button type="submit">{{ __('Log Activity') }}</x-primary-button>
            </form>
        @endcan

        <div class="space-y-3">
            @forelse ($company->crmActivities as $activity)
                <div wire:key="activity-{{ $activity->id }}" class="rounded-lg border border-gray-200 p-4">
                    <div class="flex justify-between text-sm">
                        <span class="font-medium text-gray-900">{{ $activity->activity_type->label() }}: {{ $activity->subject }}</span>
                        <span class="text-gray-500">{{ $activity->occurred_at->format('M j, Y g:i A') }}</span>
                    </div>
                    @if ($activity->body)
                        <p class="mt-2 text-sm text-gray-600">{{ $activity->body }}</p>
                    @endif
                    <p class="mt-1 text-xs text-gray-500">{{ $activity->logger?->fullName() }}</p>
                </div>
            @empty
                <p class="text-sm text-gray-500">{{ __('No activities logged yet.') }}</p>
            @endforelse
        </div>
    @endif

    @if ($activeTab === 'tasks')
        @can('create', \App\Models\Task::class)
            <form wire:submit="createTask" class="space-y-3 rounded-lg border border-gray-200 p-4">
                <div class="grid gap-4 md:grid-cols-2">
                    <div class="md:col-span-2">
                        <x-input-label for="taskTitle" :value="__('Title')" />
                        <x-text-input wire:model="taskTitle" id="taskTitle" class="mt-1 block w-full" />
                        @error('taskTitle') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
                    </div>
                    <div>
                        <x-input-label for="taskDueAt" :value="__('Due Date')" />
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
                    <div>
                        <x-input-label for="taskAssigneeId" :value="__('Assignee')" />
                        <select wire:model="taskAssigneeId" id="taskAssigneeId" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                            @foreach ($owners as $owner)
                                <option value="{{ $owner->id }}">{{ $owner->fullName() }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="md:col-span-2">
                        <x-input-label for="taskDescription" :value="__('Description')" />
                        <textarea wire:model="taskDescription" id="taskDescription" rows="2" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500"></textarea>
                    </div>
                </div>
                <x-primary-button type="submit">{{ __('Create Task') }}</x-primary-button>
            </form>
        @endcan

        <div class="space-y-3">
            @forelse ($company->tasks as $task)
                <div wire:key="task-{{ $task->id }}" class="flex items-center justify-between rounded-lg border border-gray-200 p-4">
                    <div>
                        <div class="font-medium text-gray-900">{{ $task->title }}</div>
                        <div class="mt-1 text-sm text-gray-600">
                            {{ $task->status->label() }} · {{ $task->priority->label() }} · {{ $task->due_at?->format('M j, Y') }}
                        </div>
                        <div class="text-xs text-gray-500">{{ $task->assignee?->fullName() }}</div>
                    </div>
                    <div class="flex gap-2">
                        @can('complete', $task)
                            @if ($task->status->value !== 'completed')
                                <button type="button" wire:click="completeTask('{{ $task->id }}')" class="text-sm text-green-600 hover:text-green-800">{{ __('Complete') }}</button>
                                <button type="button" wire:click="snoozeTask('{{ $task->id }}')" class="text-sm text-gray-600 hover:text-gray-800">{{ __('Snooze') }}</button>
                            @endif
                        @endcan
                    </div>
                </div>
            @empty
                <p class="text-sm text-gray-500">{{ __('No tasks yet.') }}</p>
            @endforelse
        </div>
    @endif

    @if ($activeTab === 'meetings')
        @can('create', \App\Models\CrmMeeting::class)
            <form wire:submit="scheduleMeeting" class="space-y-3 rounded-lg border border-gray-200 p-4">
                <div class="grid gap-4 md:grid-cols-2">
                    <div class="md:col-span-2">
                        <x-input-label for="meetingTitle" :value="__('Title')" />
                        <x-text-input wire:model="meetingTitle" id="meetingTitle" class="mt-1 block w-full" />
                        @error('meetingTitle') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
                    </div>
                    <div>
                        <x-input-label for="meetingStartsAt" :value="__('Starts')" />
                        <x-text-input wire:model="meetingStartsAt" id="meetingStartsAt" type="datetime-local" class="mt-1 block w-full" />
                    </div>
                    <div>
                        <x-input-label for="meetingEndsAt" :value="__('Ends')" />
                        <x-text-input wire:model="meetingEndsAt" id="meetingEndsAt" type="datetime-local" class="mt-1 block w-full" />
                    </div>
                    <div>
                        <x-input-label for="meetingLocation" :value="__('Location')" />
                        <x-text-input wire:model="meetingLocation" id="meetingLocation" class="mt-1 block w-full" />
                    </div>
                    <div>
                        <x-input-label for="meetingUrl" :value="__('Meeting URL')" />
                        <x-text-input wire:model="meetingUrl" id="meetingUrl" type="url" class="mt-1 block w-full" />
                    </div>
                    <div class="md:col-span-2">
                        <x-input-label for="meetingAgenda" :value="__('Agenda')" />
                        <textarea wire:model="meetingAgenda" id="meetingAgenda" rows="2" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500"></textarea>
                    </div>
                </div>
                <x-primary-button type="submit">{{ __('Schedule Meeting') }}</x-primary-button>
            </form>
        @endcan

        <div class="space-y-3">
            @forelse ($company->crmMeetings as $meeting)
                <div wire:key="meeting-{{ $meeting->id }}" class="rounded-lg border border-gray-200 p-4">
                    <div class="flex justify-between">
                        <div class="font-medium text-gray-900">{{ $meeting->title }}</div>
                        <span class="text-xs rounded-full bg-gray-100 px-2 py-0.5 text-gray-800">{{ $meeting->status->label() }}</span>
                    </div>
                    <div class="mt-1 text-sm text-gray-600">{{ $meeting->starts_at->format('M j, Y g:i A') }} – {{ $meeting->ends_at->format('g:i A') }}</div>
                    @if ($meeting->agenda)
                        <p class="mt-2 text-sm text-gray-600">{{ $meeting->agenda }}</p>
                    @endif
                    @can('update', $meeting)
                        @if ($meeting->status->value === 'scheduled')
                            <div class="mt-3 flex items-end gap-2">
                                <div class="flex-1">
                                    <x-input-label for="meetingOutcome" :value="__('Outcome')" />
                                    <x-text-input wire:model="meetingOutcome" id="meetingOutcome" class="mt-1 block w-full" />
                                </div>
                                <x-primary-button type="button" wire:click="completeMeeting('{{ $meeting->id }}')">{{ __('Complete') }}</x-primary-button>
                            </div>
                        @endif
                    @endcan
                </div>
            @empty
                <p class="text-sm text-gray-500">{{ __('No meetings scheduled.') }}</p>
            @endforelse
        </div>
    @endif

    @if ($activeTab === 'files')
        @can('upload', \App\Models\CrmFile::class)
            <form wire:submit="uploadFile" class="space-y-3 rounded-lg border border-gray-200 p-4">
                <div>
                    <x-input-label for="uploadFile" :value="__('File')" />
                    <input type="file" wire:model="uploadFile" id="uploadFile" class="mt-1 block w-full text-sm text-gray-600" />
                    @error('uploadFile') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
                </div>
                <div>
                    <x-input-label for="fileDescription" :value="__('Description')" />
                    <x-text-input wire:model="fileDescription" id="fileDescription" class="mt-1 block w-full" />
                </div>
                <x-primary-button type="submit">{{ __('Upload') }}</x-primary-button>
            </form>
        @endcan

        <div class="space-y-3">
            @forelse ($company->crmFiles as $file)
                <div wire:key="file-{{ $file->id }}" class="flex items-center justify-between rounded-lg border border-gray-200 p-4">
                    <div>
                        <div class="font-medium text-gray-900">{{ $file->original_name }}</div>
                        <div class="text-sm text-gray-600">{{ $file->humanSize() }} · {{ $file->created_at->format('M j, Y') }}</div>
                        @if ($file->description)
                            <div class="text-sm text-gray-500">{{ $file->description }}</div>
                        @endif
                    </div>
                    <div class="flex gap-2">
                        @can('view', $file)
                            <a href="{{ route('crm.files.download', $file) }}" class="text-sm text-indigo-600 hover:text-indigo-800">{{ __('Download') }}</a>
                        @endcan
                        @can('delete', $file)
                            <button type="button" wire:click="deleteFile('{{ $file->id }}')" wire:confirm="{{ __('Delete this file?') }}" class="text-sm text-red-600 hover:text-red-800">{{ __('Delete') }}</button>
                        @endcan
                    </div>
                </div>
            @empty
                <p class="text-sm text-gray-500">{{ __('No files uploaded.') }}</p>
            @endforelse
        </div>
    @endif

    @if ($activeTab === 'notes')
        @can('update', $company)
            <form wire:submit="saveNote" class="space-y-3 rounded-lg border border-gray-200 p-4">
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
            @forelse ($company->notes as $note)
                <div wire:key="note-{{ $note->id }}" class="rounded-lg border border-gray-200 p-4">
                    <div class="flex justify-between text-xs text-gray-500">
                        <span>{{ $note->author?->fullName() }}</span>
                        <span>{{ $note->created_at->format('M j, Y g:i A') }}</span>
                    </div>
                    <p class="mt-2 text-sm text-gray-700 whitespace-pre-wrap">{{ $note->body }}</p>
                    @if ($note->is_pinned)
                        <span class="mt-2 inline-flex rounded-full bg-yellow-100 px-2 py-0.5 text-xs text-yellow-800">{{ __('Pinned') }}</span>
                    @endif
                </div>
            @empty
                <p class="text-sm text-gray-500">{{ __('No notes yet.') }}</p>
            @endforelse
        </div>
    @endif

    @if ($activeTab === 'timeline')
        <div class="space-y-3">
            @forelse ($timeline as $entry)
                <div class="rounded-lg border border-gray-200 p-4">
                    <div class="flex justify-between text-sm">
                        <span class="font-medium text-gray-900">{{ $entry['title'] }}</span>
                        <span class="text-gray-500">{{ $entry['occurred_at']->format('M j, Y g:i A') }}</span>
                    </div>
                    @if ($entry['summary'])
                        <p class="mt-1 text-sm text-gray-600">{{ $entry['summary'] }}</p>
                    @endif
                    @if (! empty($entry['actor']))
                        <p class="mt-1 text-xs text-gray-500">{{ $entry['actor'] }}</p>
                    @endif
                </div>
            @empty
                <p class="text-sm text-gray-500">{{ __('No timeline entries yet.') }}</p>
            @endforelse
        </div>
    @endif

    @if ($activeTab === 'deal')
        @if ($company->deal)
            <div class="rounded-lg border border-gray-200 p-4 space-y-3">
                <h3 class="text-lg font-medium text-gray-900">{{ $company->deal->title }}</h3>
                <div class="grid gap-4 sm:grid-cols-2 text-sm">
                    <div><span class="font-medium text-gray-900">{{ __('Stage') }}:</span> {{ $company->deal->stage?->label }}</div>
                    <div><span class="font-medium text-gray-900">{{ __('Value') }}:</span>
                        @if ($company->deal->estimated_value)
                            {{ number_format((float) $company->deal->estimated_value, 2) }} {{ $company->deal->currency_code }}
                        @else
                            —
                        @endif
                    </div>
                    <div><span class="font-medium text-gray-900">{{ __('Owner') }}:</span> {{ $company->deal->owner?->fullName() ?? '—' }}</div>
                    <div><span class="font-medium text-gray-900">{{ __('Expected Close') }}:</span> {{ $company->deal->expected_close_date?->format('M j, Y') ?? '—' }}</div>
                </div>
                <a href="{{ route('crm.pipeline') }}" wire:navigate class="text-sm text-indigo-600 hover:text-indigo-800">{{ __('View in pipeline →') }}</a>
            </div>
        @else
            @can('create', \App\Models\Deal::class)
                <form wire:submit="createDeal" class="space-y-3 rounded-lg border border-gray-200 p-4">
                    <h3 class="text-sm font-medium text-gray-900">{{ __('Create Deal') }}</h3>
                    <div class="grid gap-4 md:grid-cols-2">
                        <div class="md:col-span-2">
                            <x-input-label for="dealTitle" :value="__('Title')" />
                            <x-text-input wire:model="dealTitle" id="dealTitle" class="mt-1 block w-full" />
                            @error('dealTitle') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
                        </div>
                        <div>
                            <x-input-label for="dealValue" :value="__('Estimated Value')" />
                            <x-text-input wire:model="dealValue" id="dealValue" type="number" step="0.01" min="0" class="mt-1 block w-full" />
                        </div>
                        <div>
                            <x-input-label for="dealCurrency" :value="__('Currency')" />
                            <x-text-input wire:model="dealCurrency" id="dealCurrency" maxlength="3" class="mt-1 block w-full" />
                        </div>
                        <div>
                            <x-input-label for="dealCloseDate" :value="__('Expected Close')" />
                            <x-text-input wire:model="dealCloseDate" id="dealCloseDate" type="date" class="mt-1 block w-full" />
                        </div>
                        <div>
                            <x-input-label for="dealStageId" :value="__('Initial Stage')" />
                            <select wire:model="dealStageId" id="dealStageId" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                                <option value="">{{ __('Default stage') }}</option>
                                @foreach ($stages as $stage)
                                    <option value="{{ $stage->id }}">{{ $stage->label }}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                    <x-primary-button type="submit">{{ __('Create Deal') }}</x-primary-button>
                </form>
            @else
                <p class="text-sm text-gray-500">{{ __('No deal exists for this company.') }}</p>
            @endcan
        @endif
    @endif
</div>
