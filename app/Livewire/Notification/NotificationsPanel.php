<?php

namespace App\Livewire\Notification;

use Livewire\Component;
use Illuminate\Support\Facades\Auth;

class NotificationsPanel extends Component
{
    public $unreadCount = 0;
    public $notifications = [];
    
    protected $listeners = [
        'refreshNotifications' => '$refresh',
    ];
    
    public function mount()
    {
        $this->loadNotifications();
    }
    
    public function loadNotifications()
    {
        $user = Auth::user();
        // Utilizando el sistema de notificaciones nativo de Laravel
        $this->notifications = $user->notifications()->latest()->take(10)->get();
        $this->unreadCount = $user->unreadNotifications()->count();
    }
    
    public function markAsRead($id)
    {
        $notification = Auth::user()->notifications()->findOrFail($id);
        $notification->markAsRead();
        $this->loadNotifications();
    }
    
    public function markAllAsRead()
    {
        Auth::user()->unreadNotifications->markAsRead();
        $this->loadNotifications();
    }

    public function render()
    {
        return view('livewire.notification.notifications-panel');
    }
}