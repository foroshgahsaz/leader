<?php

namespace App\Livewire\Notifications;

use Illuminate\Support\Collection;
use Livewire\Component;

class NotificationBell extends Component
{
    public bool $open = false;

    public function mount(): void
    {
        $this->authorize('notifications.view');
    }

    public function markAsRead(string $notificationId): void
    {
        $notification = auth()->user()->notifications()->where('id', $notificationId)->firstOrFail();
        $this->authorize('update', $notification);
        $notification->markAsRead();
    }

    public function markAllAsRead(): void
    {
        auth()->user()->unreadNotifications->markAsRead();
    }

    public function getUnreadCountProperty(): int
    {
        return auth()->user()->unreadNotifications()->count();
    }

    public function getRecentNotificationsProperty(): Collection
    {
        return auth()->user()->notifications()->latest()->limit(8)->get();
    }

    public function render()
    {
        return view('livewire.notifications.notification-bell');
    }
}
