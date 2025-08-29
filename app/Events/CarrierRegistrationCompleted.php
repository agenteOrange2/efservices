<?php

namespace App\Events;

use App\Models\User;
use App\Models\Carrier;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class CarrierRegistrationCompleted
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public $user;
    public $carrier;
    public $data;

    /**
     * Create a new event instance.
     *
     * @param User $user
     * @param Carrier $carrier
     * @param array $data
     */
    public function __construct(User $user, Carrier $carrier, array $data = [])
    {
        $this->user = $user;
        $this->carrier = $carrier;
        $this->data = $data;
    }
}