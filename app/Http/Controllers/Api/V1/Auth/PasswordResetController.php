<?php

namespace App\Http\Controllers\Api\V1\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\ForgotPasswordRequest;
use App\Http\Requests\Auth\ResetPasswordRequest;
use Illuminate\Support\Facades\Password;
use Illuminate\Support\Facades\Hash;
use App\Models\User;
use App\Models\PasswordHistory;
use OpenApi\Attributes as OA;

class PasswordResetController extends Controller
{
    #[OA\Post(
        path: "/api/v1/auth/forgot-password",
        summary: "Request a password reset link",
        tags: ["Authentication"],
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\JsonContent(
                required: ["email"],
                properties: [
                    new OA\Property(property: "email", type: "string", format: "email", example: "admin@hotelia.app")
                ]
            )
        ),
        responses: [
            new OA\Response(response: 200, description: "Reset link emailed successfully"),
            new OA\Response(response: 400, description: "Failed to send reset link"),
            new OA\Response(response: 422, description: "Validation failed")
        ]
    )]
    public function forgotPassword(ForgotPasswordRequest $request)
    {
        $status = Password::sendResetLink(
            $request->only('email')
        );

        $success = $status === Password::RESET_LINK_SENT;
        return response()->json([
            'success' => $success,
            'message' => __($status),
        ], $success ? 200 : 400);
    }

    #[OA\Post(
        path: "/api/v1/auth/reset-password",
        summary: "Reset password using token",
        tags: ["Authentication"],
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\JsonContent(
                required: ["token", "email", "password", "password_confirmation"],
                properties: [
                    new OA\Property(property: "token", type: "string", example: "valid-reset-token"),
                    new OA\Property(property: "email", type: "string", format: "email", example: "admin@hotelia.app"),
                    new OA\Property(property: "password", type: "string", format: "password", example: "NewSecret123!"),
                    new OA\Property(property: "password_confirmation", type: "string", format: "password", example: "NewSecret123!")
                ]
            )
        ),
        responses: [
            new OA\Response(response: 200, description: "Password reset successful"),
            new OA\Response(response: 422, description: "Validation or history policy failed")
        ]
    )]
    public function resetPassword(ResetPasswordRequest $request)
    {
        $status = Password::reset(
            $request->only('email', 'password', 'password_confirmation', 'token'),
            function (User $user, string $password) {

                // Validate against current password securely inside the callback
                if (Hash::check($password, $user->password)) {
                    throw \Illuminate\Validation\ValidationException::withMessages([
                        'password' => ['New password cannot be the same as your current password.'],
                    ]);
                }

                // Check against last 5 passwords securely inside the callback
                $history = $user->passwordHistories()
                    ->latest()
                    ->take(5)
                    ->get();
                
                foreach ($history as $oldPassword) {
                    if (Hash::check($password, $oldPassword->password_hash)) {
                        throw \Illuminate\Validation\ValidationException::withMessages([
                            'password' => ['You cannot reuse any of your last 5 passwords.'],
                        ]);
                    }
                }

                // Store the current password in history before changing
                PasswordHistory::create([
                    'user_id' => $user->id,
                    'password_hash' => $user->password,
                ]);

                $user->forceFill([
                    'password' => Hash::make($password),
                    'password_changed_at' => now(),
                ])->save();

                // Invalidate all sessions after password reset
                $user->tokens()->delete();
            }
        );

        $success = $status === Password::PASSWORD_RESET;
        return response()->json([
            'success' => $success,
            'message' => __($status),
        ], $success ? 200 : 422);
    }
}
