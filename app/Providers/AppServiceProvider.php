<?php

namespace App\Providers;

use App\Events\Billing\InvoiceGenerated;
use App\Events\Billing\InvoicePaid;
use App\Events\Bookings\BookingCancelled;
use App\Events\Bookings\BookingCheckedIn;
use App\Events\Bookings\BookingCheckedOut;
use App\Events\Bookings\BookingCreated;
use App\Events\Bookings\BookingUpdated;
use App\Events\Housekeeping\HousekeepingTaskCreated;
use App\Events\Housekeeping\HousekeepingTaskDeleted;
use App\Events\Housekeeping\HousekeepingTaskUpdated;
use App\Events\Maintenance\MaintenanceRequestCreated;
use App\Events\Maintenance\MaintenanceRequestDeleted;
use App\Events\Maintenance\MaintenanceRequestUpdated;
use App\Listeners\Billing\SendInvoiceNotification;
use App\Listeners\Bookings\SendBookingNotification;
use App\Listeners\Bookings\SendGuestBookingMailNotification;
use App\Listeners\Housekeeping\SendHousekeepingTaskNotification;
use App\Listeners\Maintenance\SendMaintenanceRequestNotification;
use Illuminate\Auth\Notifications\ResetPassword;
use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Event;
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
            return config('frontend.url').'/reset-password?token='.$token.'&email='.urlencode($user->email);
        });

        $this->configureRateLimiting();

        Event::listen(
            BookingCreated::class,
            SendBookingNotification::class
        );
        Event::listen(
            BookingCreated::class,
            SendGuestBookingMailNotification::class
        );

        Event::listen(
            BookingCancelled::class,
            SendBookingNotification::class
        );
        Event::listen(
            BookingCancelled::class,
            SendGuestBookingMailNotification::class
        );

        Event::listen(
            BookingUpdated::class,
            SendGuestBookingMailNotification::class
        );
        Event::listen(
            BookingUpdated::class,
            SendBookingNotification::class
        );

        Event::listen(
            BookingCheckedIn::class,
            SendBookingNotification::class
        );
        Event::listen(
            BookingCheckedOut::class,
            SendBookingNotification::class
        );
        Event::listen(
            HousekeepingTaskCreated::class,
            SendHousekeepingTaskNotification::class
        );
        Event::listen(
            HousekeepingTaskUpdated::class,
            SendHousekeepingTaskNotification::class
        );
        Event::listen(
            HousekeepingTaskDeleted::class,
            SendHousekeepingTaskNotification::class
        );
        Event::listen(
            MaintenanceRequestCreated::class,
            SendMaintenanceRequestNotification::class
        );
        Event::listen(
            MaintenanceRequestUpdated::class,
            SendMaintenanceRequestNotification::class
        );
        Event::listen(
            MaintenanceRequestDeleted::class,
            SendMaintenanceRequestNotification::class
        );
        Event::listen(
            InvoiceGenerated::class,
            SendInvoiceNotification::class
        );
        Event::listen(
            InvoicePaid::class,
            SendInvoiceNotification::class
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
