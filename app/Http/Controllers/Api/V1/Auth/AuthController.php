<?php

namespace App\Http\Controllers\Api\V1\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\LoginRequest;
use App\Http\Requests\Auth\ChangePasswordRequest;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;

use App\Models\LoginHistory;
use App\Models\FailedLoginAttempt;

use App\Models\PasswordHistory;

class AuthController extends Controller
{
    public function login(LoginRequest $request)
    {
        if (! Auth::attempt($request->validated())) {

            FailedLoginAttempt::create([
                'email' => $request->email,
                'ip_address' => $request->ip(),
                'user_agent' => $request->userAgent(),
                'attempted_at' => now(),
            ]);

            return response()->json([
                'message' => 'Invalid credentials',
            ], 401);
        }

        /** @var \App\Models\User $user */
        $user = Auth::user();

        if (! $user->is_active) {
            Auth::logout();
            return response()->json([
                'message' => 'Your account is inactive. Please contact support.',
            ], 403);
        }

        $user->update([
            'last_login_at' => now(),
        ]);

        $token = $user->createToken('hotelia-pms')->plainTextToken;

        LoginHistory::create([
            'user_id' => $user->id,
            'ip_address' => $request->ip(),
            'user_agent' => $request->userAgent(),
            'logged_in_at' => now(),
        ]);

        $user->load('roles');

        return response()->json([
            'message' => 'Login successful',
            'token' => $token,
            'user' => $user,
            'permissions' => $user->getAllPermissions()->pluck('name'),
        ]);
    }

    public function me(Request $request)
    {
        return response()->json([
            'user' => $request->user()->load('roles', 'permissions'),
        ]);
    }

    public function logout(Request $request)
    {
        LoginHistory::where('user_id', $request->user()->id)
            ->whereNull('logged_out_at')
            ->latest()
            ->first()?->update([
                'logged_out_at' => now(),
            ]);

        $request->user()
            ->currentAccessToken()
            ->delete();

        return response()->json([
            'message' => 'Logged out successfully',
        ]);
    }

    public function changePassword(ChangePasswordRequest $request)
    {
        $user = $request->user();

        // Validate current password
        if (! Hash::check($request->current_password, $user->password)) {
            return response()->json([
                'message' => 'Current password is incorrect',
            ], 422);
        }

        // Prevent reuse of the immediate current password
        if (Hash::check($request->new_password, $user->password)) {
            return response()->json([
                'message' => 'New password cannot be the same as the current password',
            ], 422);
        }

        // Check against last 5 passwords
        $history = $user->passwordHistories()
            ->latest()
            ->take(5)
            ->get();

        // Prevent reuse of any of the last 5 passwords
        foreach ($history as $oldPassword) {
            if (Hash::check($request->new_password, $oldPassword->password_hash)) {
                return response()->json([
                    'message' => 'New password cannot be the same as any of your last 5 passwords',
                ], 422);
            }
        }

        // Store the current password in history before changing
        PasswordHistory::create([
            'user_id' => $user->id,
            'password_hash' => $user->password,
        ]);

        // Update the user's password and record the change time
        $user->update([
            'password' => Hash::make($request->new_password),
            'password_changed_at' => now(),
        ]);

        // Invalidate all sessions after password change except current session
        $currentTokenId = $request->user()->currentAccessToken()->id;
        $user->tokens()
            ->where('id', '!=', $currentTokenId)
            ->delete();

        return response()->json([
            'message' => 'Password changed successfully',
        ]);
    }

    public function refreshToken(Request $request)
    {
        $user = $request->user();

        // revoke current token
        $request->user()->currentAccessToken()->delete();

        // issue new token
        $token = $user->createToken('hotelia-pms')->plainTextToken;

        return response()->json([
            'message' => 'Token refreshed successfully',
            'token' => $token,
        ]);
    }

    public function logoutAll(Request $request)
    {
        LoginHistory::where('user_id', $request->user()->id)
            ->whereNull('logged_out_at')
            ->update([
                'logged_out_at' => now(),
            ]);

        $request->user()->tokens()->delete();

        return response()->json([
            'message' => 'Logged out from all devices'
        ]);
    }

    public function status(Request $request)
    {
        return response()->json([
            'authenticated' => true,
            'user_id' => $request->user()->id,
            'roles' => $request->user()->getRoleNames(),
        ]);
    }
}
