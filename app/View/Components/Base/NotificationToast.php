<?php

namespace App\View\Components\Base;

use Closure;
use Illuminate\Contracts\View\View;
use Illuminate\View\Component;

class NotificationToast extends Component
{

    public $notification;
    /**
     * Create a new component instance.
     */
    public function __construct($notification = null)
    {
        $this->notification = $notification;
    }

    /**
     * Get the view / contents that represent the component.
     */
    public function render(): View|Closure|string
    {
        return view('components.base.notificationtoast.notification-toast');
    }
}
