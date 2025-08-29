<?php

namespace App\Events;

use App\Models\User;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class CarrierStepCompleted
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public $user;
    public $step;
    public $data;

    /**
     * Create a new event instance.
     *
     * @param User $user
     * @param string $step
     * @param array $data
     */
    public function __construct(User $user, string $step, array $data = [])
    {
        $this->user = $user;
        $this->step = $step;
        $this->data = $data;
    }
}