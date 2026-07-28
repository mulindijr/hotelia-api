<?php

namespace App\Providers;

use Illuminate\Auth\Notifications\ResetPassword;
use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\ServiceProvider;

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
        ResetPassword::createUrlUsing(function ($user, string $token) {
            return config('frontend.url') . '/reset-password?token=' . $token . '&email=' . urlencode($user->email);
        });

        $this->configureRateLimiting();

        \Illuminate\Support\Facades\Event::listen(
            \App\Events\Bookings\BookingCreated::class,
            \App\Listeners\Bookings\SendBookingNotification::class
        );
        \Illuminate\Support\Facades\Event::listen(
            \App\Events\Bookings\BookingCreated::class,
            \App\Listeners\Bookings\SendGuestBookingMailNotification::class
        );

        \Illuminate\Support\Facades\Event::listen(
            \App\Events\Bookings\BookingCancelled::class,
            \App\Listeners\Bookings\SendBookingNotification::class
        );
        \Illuminate\Support\Facades\Event::listen(
            \App\Events\Bookings\BookingCancelled::class,
            \App\Listeners\Bookings\SendGuestBookingMailNotification::class
        );

        \Illuminate\Support\Facades\Event::listen(
            \App\Events\Bookings\BookingUpdated::class,
            \App\Listeners\Bookings\SendGuestBookingMailNotification::class
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

    /**
     * Configure the rate limiters for the application.
     */
    protected function configureRateLimiting(): void
    {
        // Public auth endpoints (login, forgot-password, reset-password)
        // 5 attempts per minute per IP to prevent brute-force attacks
        RateLimiter::for('auth-login', function (Request $request) {
            return Limit::perMinute(5)->by($request->ip());
        });

        // Sensitive authenticated endpoints (change-password, refresh-token)
        // 10 attempts per minute per authenticated user
        RateLimiter::for('auth-sensitive', function (Request $request) {
            return Limit::perMinute(10)->by($request->user()?->id ?: $request->ip());
        });

        // Global API throttle for all authenticated routes
        // 60 requests per minute per authenticated user
        RateLimiter::for('api', function (Request $request) {
            return Limit::perMinute(60)->by($request->user()?->id ?: $request->ip());
        });
    }
}
