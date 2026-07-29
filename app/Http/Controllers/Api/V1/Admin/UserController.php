<?php

namespace App\Http\Controllers\Api\V1\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use OpenApi\Attributes as OA;

class UserController extends Controller
{
    #[OA\Post(
        path: '/api/v1/admin/users/{user}/unlock',
        summary: 'Unlock a locked user account (requires update users permission)',
        tags: ['Security'],
        security: [['sanctum' => []]],
        parameters: [
            new OA\Parameter(name: 'user', in: 'path', required: true, schema: new OA\Schema(type: 'integer')),
        ],
        responses: [
            new OA\Response(response: 200, description: 'User account unlocked successfully'),
            new OA\Response(response: 400, description: 'User account is not locked'),
            new OA\Response(response: 401, description: 'Unauthenticated'),
            new OA\Response(response: 403, description: 'Forbidden'),
        ]
    )]
    public function unlock(User $user)
    {
        // Check if user is locked
        if (! $user->isLocked()) {
            return response()->json([
                'message' => 'User account is not locked.',
            ], 400);
        }

        // Unlock the user using our model helper
        $user->unlock();

        return response()->json([
            'message' => "Account for {$user->full_name} has been unlocked successfully.",
        ]);
    }
}
