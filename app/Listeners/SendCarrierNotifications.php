<?php

namespace App\Listeners;

use App\Events\CarrierStepCompleted;
use App\Events\CarrierRegistrationCompleted;
use App\Services\NotificationService;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Support\Facades\Log;

class SendCarrierNotifications // implements ShouldQueue
{
    // use InteractsWithQueue;

    protected $notificationService;

    /**
     * Create the event listener.
     *
     * @param NotificationService $notificationService
     */
    public function __construct(NotificationService $notificationService)
    {
        $this->notificationService = $notificationService;
    }

    /**
     * Handle the CarrierStepCompleted event.
     *
     * @param CarrierStepCompleted $event
     * @return void
     */
    public function handleStepCompleted(CarrierStepCompleted $event)
    {
        Log::info('SendCarrierNotifications: handleStepCompleted called', [
            'user_id' => $event->user->id,
            'step' => $event->step,
            'data' => $event->data
        ]);
        
        try {
            $this->notificationService->sendStepCompletedNotification(
                $event->user,
                $event->step,
                $event->data
            );
        } catch (\Exception $e) {
            Log::error('Error sending carrier step completed notification', [
                'user_id' => $event->user->id,
                'step' => $event->step,
                'error' => $e->getMessage()
            ]);
        }
    }

    /**
     * Handle the CarrierRegistrationCompleted event.
     *
     * @param CarrierRegistrationCompleted $event
     * @return void
     */
    public function handleRegistrationCompleted(CarrierRegistrationCompleted $event)
    {
        Log::info('SendCarrierNotifications: handleRegistrationCompleted called', [
            'user_id' => $event->user->id,
            'carrier_id' => $event->carrier->id,
            'data' => $event->data
        ]);
        
        try {
            $this->notificationService->sendRegistrationCompletedNotification(
                $event->user,
                $event->carrier,
                $event->data
            );
        } catch (\Exception $e) {
            Log::error('Error sending carrier registration completed notification', [
                'user_id' => $event->user->id,
                'carrier_id' => $event->carrier->id,
                'error' => $e->getMessage()
            ]);
        }
    }
}