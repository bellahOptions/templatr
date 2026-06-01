<?php

namespace App\Livewire;

use App\Models\Notification;
use Livewire\Component;

class NotificationBell extends Component
{
    public int $unreadCount = 0;
    public array $notifications = [];
    public bool $showDropdown = false;

    public function mount()
    {
        if (auth()->check()) {
            $this->refreshNotifications();
        }
    }

    public function refreshNotifications()
    {
        if (!auth()->check()) {
            return;
        }

        $query = Notification::forUser(auth()->id())->active()->latest()->take(10);
        $this->unreadCount = (clone $query)->unread()->count();
        $this->notifications = $query->get()->toArray();
    }

    public function toggleDropdown()
    {
        $this->showDropdown = !$this->showDropdown;
        if ($this->showDropdown) {
            $this->refreshNotifications();
        }
    }

    public function markAsRead($notificationId)
    {
        $notification = Notification::find($notificationId);
        if ($notification && auth()->check()) {
            $notification->markAsRead();
            $this->refreshNotifications();
        }
    }

    public function markAllAsRead()
    {
        if (auth()->check()) {
            Notification::forUser(auth()->id())->unread()->update([
                'is_read' => true,
                'read_at' => now(),
            ]);
            $this->refreshNotifications();
        }
    }

    public function render()
    {
        return view('livewire.notification-bell');
    }
}
