<?php

namespace App\Http\Controllers\Api\V1\Security;

use App\Http\Controllers\Controller;
use App\Models\FailedLoginAttempt;
use Illuminate\Http\Request;
use OpenApi\Attributes as OA;

#[OA\Tag(name: 'Security', description: 'Security logs and system activity tracking endpoints')]
class SecurityController extends Controller
{
    #[OA\Get(
        path: '/api/v1/login-history',
        summary: 'Display a listing of login history for the authenticated user',
        tags: ['Security'],
        security: [['sanctum' => []]],
        responses: [
            new OA\Response(response: 200, description: 'Login history retrieved successfully'),
            new OA\Response(response: 401, description: 'Unauthenticated'),
        ]
    )]
    public function loginHistory(Request $request)
    {
        return response()->json(
            $request->user()
                ->loginHistories()
                ->latest()
                ->paginate()
        );
    }

    #[OA\Get(
        path: '/api/v1/failed-logins',
        summary: 'Display a listing of all failed login attempts (requires view activity logs permission)',
        tags: ['Security'],
        security: [['sanctum' => []]],
        responses: [
            new OA\Response(response: 200, description: 'Failed logins log retrieved successfully'),
            new OA\Response(response: 401, description: 'Unauthenticated'),
            new OA\Response(response: 403, description: 'Forbidden'),
        ]
    )]
    public function failedLogins()
    {
        return response()->json(
            FailedLoginAttempt::latest()
                ->paginate()
        );
    }
}
