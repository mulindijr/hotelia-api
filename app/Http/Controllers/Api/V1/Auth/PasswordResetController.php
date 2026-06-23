<?php

namespace App\Http\Controllers\Api\V1\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\ForgotPasswordRequest;
use App\Http\Requests\Auth\ResetPasswordRequest;
use Illuminate\Support\Facades\Password;
use Illuminate\Support\Facades\Hash;

use App\Models\User;
use App\Models\PasswordHistory;

class PasswordResetController extends Controller
{
    public function forgotPassword(ForgotPasswordRequest $request)
    {
        $status = Password::sendResetLink(
            $request->only('email')
        );

        return response()->json([
            'message' => __($status),
        ], $status === Password::RESET_LINK_SENT ? 200 : 400);
    }

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

        return response()->json([
            'message' => __($status),
        ], $status === Password::PASSWORD_RESET ? 200 : 422);
    }
}
