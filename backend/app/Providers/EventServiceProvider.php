<?php

namespace App\Providers;

use Illuminate\Auth\Events\Registered;
use Illuminate\Auth\Listeners\SendEmailVerificationNotification;
use Illuminate\Foundation\Support\Providers\EventServiceProvider as ServiceProvider;
use Illuminate\Support\Facades\Event;

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

        // Phase 2: Loyalty Program Events
        \App\Events\OrderCompleted::class => [
            \App\Listeners\AwardLoyaltyPoints::class,
        ],

        \App\Events\OrderStatusChanged::class => [
            // Add listeners here when needed
        ],

        \App\Events\OrderRefunded::class => [
            // Add listeners here when needed (e.g., RefundLoyaltyPoints)
        ],
    ];

    /**
     * Register any events for your application.
     */
    public function boot(): void
    {
        //
    }

    /**
     * Determine if events and listeners should be automatically discovered.
     */
    public function shouldDiscoverEvents(): bool
    {
        return false;
    }
}
