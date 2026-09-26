<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class InitializeTenancyByAuthUser
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();

        if ($user && $user->tenant_id) {
            $tenant = \App\Models\Tenant::find($user->tenant_id);
            if ($tenant) {
                tenancy()->initialize($tenant);
            } else {
                return response()->json(['message' => 'Tenant not found.'], 403);
            }
        } elseif ($user && !$user->tenant_id) {
            // Optional: Handle Super Admin globally if they don't have a tenant
            // Or reject if every user must belong to a tenant
            if (!$user->isSuperAdmin()) {
                return response()->json(['message' => 'User does not belong to a tenant.'], 403);
            }
        }

        return $next($request);
    }
}
