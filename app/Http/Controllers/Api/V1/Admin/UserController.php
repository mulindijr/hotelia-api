<?php

namespace App\Http\Controllers\Api\V1\Admin;

use App\Http\Controllers\Controller;

use App\Models\User;

class UserController extends Controller
{
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
            'message' => "Account for {$user->full_name} has been unlocked successfully."
        ]);
    }
}
