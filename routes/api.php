<?php

use Illuminate\Support\Facades\Route;

Route::prefix('v1')
  ->scopeBindings()
  ->group(function () {
    require base_path('routes/api/auth.php');
    require base_path('routes/api/security.php');
    require base_path('routes/api/admin.php');
    require base_path('routes/api/hotels.php');
    require base_path('routes/api/rooms.php');
    require base_path('routes/api/guests.php');
    require base_path('routes/api/services.php');
    require base_path('routes/api/housekeeping.php');
    require base_path('routes/api/maintenance.php');
  });
