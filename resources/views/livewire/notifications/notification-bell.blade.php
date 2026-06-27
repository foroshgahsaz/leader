<div class="relative" x-data="{ open: @entangle('open') }">
    <button type="button" @click="open = !open" class="relative rounded-md p-2 text-gray-500 hover:text-gray-700 hover:bg-gray-100 focus:outline-none">
        <svg class="h-6 w-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9" />
        </svg>
        @if ($this->unreadCount > 0)
            <span class="absolute top-1 right-1 inline-flex h-4 min-w-4 items-center justify-center rounded-full bg-red-500 px-1 text-[10px] font-bold text-white">
                {{ $this->unreadCount > 9 ? '9+' : $this->unreadCount }}
            </span>
        @endif
    </button>

    <div x-show="open" x-cloak @click.outside="open = false" class="absolute right-0 z-50 mt-2 w-96 origin-top-right rounded-md bg-white shadow-lg ring-1 ring-black ring-opacity-5">
        <div class="flex items-center justify-between border-b border-gray-100 px-4 py-3">
            <h3 class="text-sm font-semibold text-gray-900">{{ __('Notifications') }}</h3>
            @if ($this->unreadCount > 0)
                <button type="button" wire:click="markAllAsRead" class="text-xs text-indigo-600 hover:text-indigo-800">{{ __('Mark all read') }}</button>
            @endif
        </div>
        <div class="max-h-96 overflow-y-auto">
            @forelse ($this->recentNotifications as $notification)
                @php($data = $notification->data)
                <div wire:key="notification-{{ $notification->id }}" @class(['px-4 py-3 border-b border-gray-50', 'bg-indigo-50/40' => is_null($notification->read_at)])>
                    <div class="flex items-start justify-between gap-3">
                        <div>
                            <p class="text-sm font-medium text-gray-900">{{ $data['title'] ?? __('Notification') }}</p>
                            <p class="mt-1 text-sm text-gray-600">{{ $data['body'] ?? '' }}</p>
                            <p class="mt-1 text-xs text-gray-400">{{ $notification->created_at->diffForHumans() }}</p>
                        </div>
                        @if (is_null($notification->read_at))
                            <button type="button" wire:click="markAsRead('{{ $notification->id }}')" class="text-xs text-indigo-600">{{ __('Read') }}</button>
                        @endif
                    </div>
                    @if (! empty($data['url']))
                        <a href="{{ $data['url'] }}" wire:navigate class="mt-2 inline-block text-xs font-medium text-indigo-600 hover:text-indigo-800">{{ __('View') }}</a>
                    @endif
                </div>
            @empty
                <div class="px-4 py-8 text-center text-sm text-gray-500">{{ __('No notifications yet.') }}</div>
            @endforelse
        </div>
        <div class="border-t border-gray-100 px-4 py-3">
            <a href="{{ route('settings.notifications') }}" wire:navigate class="text-sm font-medium text-indigo-600 hover:text-indigo-800">{{ __('View all notifications') }}</a>
        </div>
    </div>
</div>
