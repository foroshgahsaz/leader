<div class="space-y-6">
    @if (session('status'))
        <div class="rounded-md bg-green-50 p-4 text-sm text-green-800">{{ session('status') }}</div>
    @endif

    <div class="flex flex-wrap gap-2">
        <a href="{{ route('crm.companies.index') }}" wire:navigate class="text-sm text-indigo-600 hover:text-indigo-800">{{ __('Companies') }}</a>
        <span class="text-gray-300">|</span>
        <a href="{{ route('crm.pipeline') }}" wire:navigate class="text-sm text-indigo-600 hover:text-indigo-800">{{ __('Pipeline') }}</a>
        <span class="text-gray-300">|</span>
        <a href="{{ route('crm.tasks') }}" wire:navigate class="text-sm text-indigo-600 hover:text-indigo-800">{{ __('Tasks') }}</a>
    </div>

    <div class="grid gap-4 md:grid-cols-3">
        <div>
            <x-input-label for="status" :value="__('Status')" />
            <select wire:model.live="status" id="status" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                <option value="">{{ __('Open tasks') }}</option>
                @foreach ($statuses as $value => $label)
                    <option value="{{ $value }}">{{ $label }}</option>
                @endforeach
            </select>
        </div>
        <div>
            <x-input-label for="assigneeId" :value="__('Assignee')" />
            <select wire:model.live="assigneeId" id="assigneeId" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                <option value="">{{ __('All assignees') }}</option>
                @foreach ($assignees as $assignee)
                    <option value="{{ $assignee->id }}">{{ $assignee->fullName() }}</option>
                @endforeach
            </select>
        </div>
        <div class="flex items-end">
            <label class="inline-flex items-center gap-2 text-sm text-gray-600">
                <input type="checkbox" wire:model.live="overdueOnly" class="rounded border-gray-300 text-indigo-600 focus:ring-indigo-500">
                {{ __('Overdue only') }}
            </label>
        </div>
    </div>

    <div class="overflow-hidden border border-gray-200 rounded-lg">
        <table class="min-w-full divide-y divide-gray-200 text-sm">
            <thead class="bg-gray-50">
                <tr>
                    <th class="px-4 py-3 text-left font-medium text-gray-600">{{ __('Task') }}</th>
                    <th class="px-4 py-3 text-left font-medium text-gray-600">{{ __('Company') }}</th>
                    <th class="px-4 py-3 text-left font-medium text-gray-600">{{ __('Assignee') }}</th>
                    <th class="px-4 py-3 text-left font-medium text-gray-600">{{ __('Due') }}</th>
                    <th class="px-4 py-3 text-left font-medium text-gray-600">{{ __('Status') }}</th>
                    <th class="px-4 py-3 text-left font-medium text-gray-600">{{ __('Priority') }}</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-200 bg-white">
                @forelse ($tasks as $task)
                    <tr wire:key="task-{{ $task->id }}">
                        <td class="px-4 py-3 font-medium text-gray-900">{{ $task->title }}</td>
                        <td class="px-4 py-3">
                            @if ($task->buyer)
                                <a href="{{ route('crm.companies.show', $task->buyer) }}" wire:navigate class="text-indigo-600 hover:text-indigo-800">{{ $task->buyer->name }}</a>
                            @else
                                —
                            @endif
                        </td>
                        <td class="px-4 py-3 text-gray-600">{{ $task->assignee?->fullName() ?? '—' }}</td>
                        <td class="px-4 py-3 text-gray-600 @if($task->due_at && $task->due_at->isPast() && $task->status->value === 'pending') text-red-600 font-medium @endif">
                            {{ $task->due_at?->format('M j, Y') ?? '—' }}
                        </td>
                        <td class="px-4 py-3">
                            <span class="inline-flex rounded-full bg-gray-100 px-2 py-0.5 text-xs font-medium text-gray-800">{{ $task->status->label() }}</span>
                        </td>
                        <td class="px-4 py-3 text-gray-600">{{ $task->priority->label() }}</td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="6" class="px-4 py-8 text-center text-gray-500">{{ __('No tasks found.') }}</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    {{ $tasks->links() }}
</div>
