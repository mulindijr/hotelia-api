<?php

use Illuminate\Support\Facades\Route;

Route::prefix('v1')
  ->scopeBindings()
  ->group(function () {
    require base_path('routes/api/auth.php');
    require base_path('routes/api/security.php');
    require base_path('routes/api/admin.php');
    require base_path('routes/api/hotels.php');
  });
