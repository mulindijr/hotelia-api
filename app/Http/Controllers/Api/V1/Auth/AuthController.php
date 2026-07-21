<?php

namespace App\Http\Controllers\Api\V1\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\LoginRequest;
use App\Http\Requests\Auth\ChangePasswordRequest;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Carbon;

use App\Models\LoginHistory;
use App\Models\FailedLoginAttempt;

use App\Models\PasswordHistory;

use OpenApi\Attributes as OA;

#[OA\Tag(name: "Authentication", description: "User authentication and password management endpoints")]
class AuthController extends Controller
{
    #[OA\Post(
        path: "/api/v1/auth/login",
        summary: "Authenticate user and issue Sanctum token",
        tags: ["Authentication"],
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\JsonContent(
                required: ["email", "password"],
                properties: [
                    new OA\Property(property: "email", type: "string", format: "email", example: "admin@hotelia.app"),
                    new OA\Property(property: "password", type: "string", format: "password", example: "Secret123!")
                ]
            )
        ),
        responses: [
            new OA\Response(response: 200, description: "Login successful"),
            new OA\Response(response: 401, description: "Invalid credentials"),
            new OA\Response(response: 423, description: "Account temporarily locked"),
            new OA\Response(response: 429, description: "Too many login attempts")
        ]
    )]
    public function login(LoginRequest $request)
    {
        // Check if user exists
        $user = \App\Models\User::where('email', $request->email)->first();

        // Check if user is locked
        if ($user && $user->locked_until && now()->lessThan($user->locked_until)) {

            return response()->json([
                'message' => 'Account is temporarily locked.',
                'locked_until' => $user->locked_until,
                'seconds_remaining' => now()->diffInSeconds($user->locked_until),
            ], 423);
        }

        // Attempt login
        if (! Auth::attempt($request->validated())) {

            FailedLoginAttempt::create([
                'email' => $request->email,
                'ip_address' => $request->ip(),
                'user_agent' => $request->userAgent(),
                'attempted_at' => now(),
            ]);

            if ($user) {

                $user->increment('failed_login_count');

                if ($user->failed_login_count >= 3) {

                    $user->update([
                        'locked_until' => now()->addMinutes(15),
                        'failed_login_count' => 0, // Reset the count after locking
                    ]);
                }
            }

            return response()->json([
                'message' => 'Invalid credentials',
            ], 401);
        }

        // Authenticated user operations (safe from locked accounts)
        /** @var \App\Models\User $user */
        $user = Auth::user();

        // Reset counts on successful login
        $user->update([
            'failed_login_count' => 0,
            'locked_until' => null,
            'last_login_at' => now(),
        ]);

        if (! $user->is_active) {
            Auth::logout();
            return response()->json([
                'message' => 'Your account is inactive. Please contact support.',
            ], 403);
        }

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

    #[OA\Get(
        path: "/api/v1/auth/me",
        summary: "Retrieve current user profile with roles and permissions",
        tags: ["Authentication"],
        security: [["bearerAuth" => []]],
        responses: [
            new OA\Response(response: 200, description: "User profile retrieved"),
            new OA\Response(response: 401, description: "Unauthenticated")
        ]
    )]
    public function me(Request $request)
    {
        return response()->json([
            'user' => $request->user()->load('roles', 'permissions'),
        ]);
    }

    #[OA\Post(
        path: "/api/v1/auth/logout",
        summary: "Logout and revoke current token",
        tags: ["Authentication"],
        security: [["bearerAuth" => []]],
        responses: [
            new OA\Response(response: 200, description: "Logged out successfully"),
            new OA\Response(response: 401, description: "Unauthenticated")
        ]
    )]
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

    #[OA\Post(
        path: "/api/v1/auth/change-password",
        summary: "Change user password with complexity and history checks",
        tags: ["Authentication"],
        security: [["bearerAuth" => []]],
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\JsonContent(
                required: ["current_password", "new_password", "new_password_confirmation"],
                properties: [
                    new OA\Property(property: "current_password", type: "string", format: "password"),
                    new OA\Property(property: "new_password", type: "string", format: "password"),
                    new OA\Property(property: "new_password_confirmation", type: "string", format: "password")
                ]
            )
        ),
        responses: [
            new OA\Response(response: 200, description: "Password changed successfully"),
            new OA\Response(response: 422, description: "Validation failed or password reuse violation")
        ]
    )]
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
        $currentTokenId = $request->user()->currentAccessToken()?->id;
        if ($currentTokenId) {
            $user->tokens()
                ->where('id', '!=', $currentTokenId)
                ->delete();
        } else {
            $user->tokens()->delete();
        }

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
