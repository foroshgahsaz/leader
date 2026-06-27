<?php

namespace App\Livewire\Notifications;

use Livewire\Attributes\Layout;
use Livewire\Component;
use Livewire\WithPagination;

class NotificationIndex extends Component
{
    use WithPagination;

    public string $filter = 'all';

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

    public function updatedFilter(): void
    {
        $this->resetPage();
    }

    #[Layout('layouts.settings', ['heading' => 'Notifications', 'subheading' => 'Stay up to date with team and account activity.'])]
    public function render()
    {
        $query = auth()->user()->notifications()->latest();

        if ($this->filter === 'unread') {
            $query->whereNull('read_at');
        }

        return view('livewire.notifications.notification-index', [
            'notifications' => $query->paginate(15),
        ]);
    }
}
