<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class SetHotelPermissionsContext
{
    public function handle(Request $request, Closure $next): Response
    {
        $hotelId = $request->header('X-Hotel-ID');
        
        if ($hotelId) {
            setPermissionsTeamId($hotelId);
        }

        return $next($request);
    }
}
