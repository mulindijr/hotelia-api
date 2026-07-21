<?php

use App\Jobs\AutoCancelStaleBookings;
use App\Jobs\NotifyStuckCleaningRooms;
use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

// Schedule automated jobs every 15 minutes
Schedule::job(new AutoCancelStaleBookings)->everyFifteenMinutes();
Schedule::job(new NotifyStuckCleaningRooms)->everyFifteenMinutes();

