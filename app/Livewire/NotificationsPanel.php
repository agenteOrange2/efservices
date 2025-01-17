<?php

namespace App\Livewire;

use Livewire\Component;
use App\Models\Notification;
use Livewire\WithPagination;

class NotificationsPanel extends Component
{

    use WithPagination;

    public $unreadCount = 0;

    public function mount()
    {
        $this->updateUnreadCount();
    }

    public function getListeners()
    {
        return [
            'echo:notifications,NewNotification' => 'handleNewNotification',
            'notificationRead' => 'updateUnreadCount'
        ];
    }

    public function markAsRead($notificationId)
    {
        $notification = Notification::find($notificationId);
        if ($notification && $notification->user_id === auth()->id()) {
            $notification->update(['is_read' => true]);
            $this->updateUnreadCount();
        }
    }

    public function markAllAsRead()
    {
        Notification::where('user_id', auth()->id())
            ->where('is_read', false)
            ->update(['is_read' => true]);
            
        $this->updateUnreadCount();
    }

    private function updateUnreadCount()
    {
        $this->unreadCount = Notification::where('user_id', auth()->id())
            ->where('is_read', false)
            ->count();
    }

    public function render()
    {
        $notifications = Notification::where('user_id', auth()->id())
            ->with('notificationType')
            ->orderBy('sent_at', 'desc')
            ->paginate(10);

        return view('livewire.notifications-panel', [
            'notifications' => $notifications
        ]);
    }
}
