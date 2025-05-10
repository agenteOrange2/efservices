<?php

namespace App\Livewire\Notification;

use Livewire\Component;
use Illuminate\Support\Facades\Auth;

class NotificationCounter extends Component
{
    public $count = 0;
    
    protected $listeners = ['refreshNotifications' => '$refresh'];
    
    public function mount()
    {
        $this->count = Auth::user()->unreadNotifications()->count();
    }
    
    public function render()
    {
        return view('livewire.notification.notification-counter');
    }
}