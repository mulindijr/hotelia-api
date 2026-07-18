<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Auth\Notifications\ResetPassword;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        ResetPassword::createUrlUsing(function($user, string $token) {
            return config('frontend.url') . '/reset-password?token='. $token. '&email='. urlencode($user->email);
        });

        \Illuminate\Support\Facades\Event::listen(
            \App\Events\Bookings\BookingCreated::class,
            \App\Listeners\Bookings\SendBookingNotification::class
        );
        \Illuminate\Support\Facades\Event::listen(
            \App\Events\Bookings\BookingCancelled::class,
            \App\Listeners\Bookings\SendBookingNotification::class
        );
        \Illuminate\Support\Facades\Event::listen(
            \App\Events\Bookings\BookingCheckedIn::class,
            \App\Listeners\Bookings\SendBookingNotification::class
        );
        \Illuminate\Support\Facades\Event::listen(
            \App\Events\Bookings\BookingCheckedOut::class,
            \App\Listeners\Bookings\SendBookingNotification::class
        );
        \Illuminate\Support\Facades\Event::listen(
            \App\Events\Housekeeping\HousekeepingTaskCreated::class,
            \App\Listeners\Housekeeping\SendHousekeepingTaskNotification::class
        );
        \Illuminate\Support\Facades\Event::listen(
            \App\Events\Housekeeping\HousekeepingTaskUpdated::class,
            \App\Listeners\Housekeeping\SendHousekeepingTaskNotification::class
        );
        \Illuminate\Support\Facades\Event::listen(
            \App\Events\Maintenance\MaintenanceRequestCreated::class,
            \App\Listeners\Maintenance\SendMaintenanceRequestNotification::class
        );
    }
}
