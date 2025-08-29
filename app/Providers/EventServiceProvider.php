<?php

namespace App\Providers;

use Illuminate\Auth\Events\Registered;
use Illuminate\Auth\Listeners\SendEmailVerificationNotification;
use Illuminate\Foundation\Support\Providers\EventServiceProvider as ServiceProvider;
use Illuminate\Support\Facades\Event;
use App\Events\CarrierStepCompleted;
use App\Events\CarrierRegistrationCompleted;
use App\Listeners\SendCarrierNotifications;

class EventServiceProvider extends ServiceProvider
{
    /**
     * The event to listener mappings for the application.
     *
     * @var array<class-string, array<int, class-string>>
     */
    protected $listen = [
        Registered::class => [
            SendEmailVerificationNotification::class,
        ],
        
        // Eventos del sistema de notificaciones de carriers
        CarrierStepCompleted::class => [
            SendCarrierNotifications::class . '@handleStepCompleted',
        ],
        
        CarrierRegistrationCompleted::class => [
            SendCarrierNotifications::class . '@handleRegistrationCompleted',
        ],
    ];

    /**
     * Register any events for your application.
     */
    public function boot(): void
    {
        // Registrar eventos manualmente para asegurar que funcionen
        Event::listen(CarrierStepCompleted::class, [SendCarrierNotifications::class, 'handleStepCompleted']);
        Event::listen(CarrierRegistrationCompleted::class, [SendCarrierNotifications::class, 'handleRegistrationCompleted']);
    }

    /**
     * Determine if events and listeners should be automatically discovered.
     */
    public function shouldDiscoverEvents(): bool
    {
        return false;
    }
}