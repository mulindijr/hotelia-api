<?php

namespace Tests\Feature\Api\V1\Auth;

use App\Models\User;
use App\Models\PasswordHistory;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Password;
use Tests\ApiTestCase;

class PasswordResetTest extends ApiTestCase
{
    public function test_user_can_request_password_reset_link(): void
    {
        $user = User::factory()->create([
            'email' => 'user@example.com',
        ]);

        $response = $this->postJson(route('auth.forgot-password'), [
            'email' => 'user@example.com',
        ]);

        $response->assertOk()
            ->assertJsonPath('success', true)
            ->assertJsonStructure(['success', 'message']);
    }

    public function test_user_cannot_request_link_for_non_existent_email(): void
    {
        $response = $this->postJson(route('auth.forgot-password'), [
            'email' => 'nonexistent@example.com',
        ]);

        $response->assertStatus(400)
            ->assertJsonPath('success', false);
    }

    public function test_user_can_reset_password_with_valid_token(): void
    {
        $user = User::factory()->create([
            'email' => 'user@example.com',
            'password' => Hash::make('OldPassword123!'),
        ]);

        $token = Password::createToken($user);

        $response = $this->postJson(route('auth.reset-password'), [
            'email' => 'user@example.com',
            'token' => $token,
            'password' => 'NewPassword123!',
            'password_confirmation' => 'NewPassword123!',
        ]);

        $response->assertOk()
            ->assertJsonPath('success', true);

        $this->assertTrue(Hash::check('NewPassword123!', $user->refresh()->password));
    }

    public function test_user_cannot_reset_password_to_current_password(): void
    {
        $user = User::factory()->create([
            'email' => 'user@example.com',
            'password' => Hash::make('CurrentPassword123!'),
        ]);

        $token = Password::createToken($user);

        $response = $this->postJson(route('auth.reset-password'), [
            'email' => 'user@example.com',
            'token' => $token,
            'password' => 'CurrentPassword123!',
            'password_confirmation' => 'CurrentPassword123!',
        ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['password']);
    }

    public function test_user_cannot_reset_password_to_recent_password_in_history(): void
    {
        $user = User::factory()->create([
            'email' => 'user@example.com',
            'password' => Hash::make('ActivePassword123!'),
        ]);

        // Create password history entries
        PasswordHistory::create([
            'user_id' => $user->id,
            'password_hash' => Hash::make('OldHistoryPassword123!'),
        ]);

        $token = Password::createToken($user);

        $response = $this->postJson(route('auth.reset-password'), [
            'email' => 'user@example.com',
            'token' => $token,
            'password' => 'OldHistoryPassword123!',
            'password_confirmation' => 'OldHistoryPassword123!',
        ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['password']);
    }
}
