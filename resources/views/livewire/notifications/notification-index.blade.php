<div class="space-y-4">
    <div class="flex items-center justify-between gap-4">
        <select wire:model.live="filter" class="border-gray-300 rounded-md text-sm">
            <option value="all">{{ __('All') }}</option>
            <option value="unread">{{ __('Unread') }}</option>
        </select>
        <button type="button" wire:click="markAllAsRead" class="text-sm text-indigo-600 hover:text-indigo-800">{{ __('Mark all as read') }}</button>
    </div>

    <div class="divide-y divide-gray-200 border border-gray-200 rounded-lg overflow-hidden">
        @forelse ($notifications as $notification)
            @php($data = $notification->data)
            <div wire:key="notification-row-{{ $notification->id }}" @class(['p-4 bg-white', 'bg-indigo-50/30' => is_null($notification->read_at)])>
                <div class="flex items-start justify-between gap-4">
                    <div>
                        <p class="font-medium text-gray-900">{{ $data['title'] ?? __('Notification') }}</p>
                        <p class="mt-1 text-sm text-gray-600">{{ $data['body'] ?? '' }}</p>
                        <p class="mt-2 text-xs text-gray-400">{{ $notification->created_at->format('M j, Y g:i A') }}</p>
                        @if (! empty($data['url']))
                            <a href="{{ $data['url'] }}" wire:navigate class="mt-2 inline-block text-sm text-indigo-600 hover:text-indigo-800">{{ __('Open') }}</a>
                        @endif
                    </div>
                    @if (is_null($notification->read_at))
                        <button type="button" wire:click="markAsRead('{{ $notification->id }}')" class="text-sm text-indigo-600">{{ __('Mark read') }}</button>
                    @endif
                </div>
            </div>
        @empty
            <div class="p-8 text-center text-gray-500">{{ __('No notifications found.') }}</div>
        @endforelse
    </div>

    {{ $notifications->links() }}
</div>
