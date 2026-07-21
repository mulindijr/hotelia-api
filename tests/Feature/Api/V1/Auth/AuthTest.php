<?php

namespace Tests\Feature\Api\V1\Auth;

use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\RateLimiter;
use Tests\ApiTestCase;

class AuthTest extends ApiTestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        // Clear rate limiter state between tests so each test starts clean
        RateLimiter::clear('auth-login|' . request()->ip());
    }

    /**
     * Test successful login with valid credentials.
     */
    public function test_user_can_login_with_valid_credentials(): void
    {
        $user = User::factory()->create([
            'email'     => 'auth.test@example.com',
            'password'  => Hash::make('password123'),
            'is_active' => true,
        ]);

        $response = $this->postJson(route('auth.login'), [
            'email'    => 'auth.test@example.com',
            'password' => 'password123',
        ]);

        $response->assertOk()
            ->assertJsonPath('message', 'Login successful')
            ->assertJsonStructure(['token', 'user', 'permissions']);
    }

    /**
     * Test login failure with invalid credentials.
     */
    public function test_login_fails_with_invalid_credentials(): void
    {
        User::factory()->create([
            'email'    => 'bad.auth@example.com',
            'password' => Hash::make('password123'),
        ]);

        $response = $this->postJson(route('auth.login'), [
            'email'    => 'bad.auth@example.com',
            'password' => 'wrongpassword',
        ]);

        $response->assertStatus(401)
            ->assertJsonPath('message', 'Invalid credentials');
    }

    /**
     * Test that the standardized 401 error payload is returned for unauthenticated requests.
     */
    public function test_unauthenticated_request_returns_standardized_401(): void
    {
        $response = $this->getJson(route('auth.me'));

        $response->assertStatus(401)
            ->assertJsonStructure(['success', 'message'])
            ->assertJsonPath('success', false)
            ->assertJsonPath('message', 'Unauthenticated.');
    }

    /**
     * Test that login rate limiter blocks after 5 attempts.
     */
    public function test_login_rate_limiter_blocks_after_five_attempts(): void
    {
        User::factory()->create([
            'email'    => 'ratelimit@example.com',
            'password' => Hash::make('password123'),
        ]);

        // Hit the endpoint 5 times with wrong credentials
        for ($i = 0; $i < 5; $i++) {
            $this->postJson(route('auth.login'), [
                'email'    => 'ratelimit@example.com',
                'password' => 'wrongpassword',
            ]);
        }

        // The 6th attempt should be throttled
        $response = $this->postJson(route('auth.login'), [
            'email'    => 'ratelimit@example.com',
            'password' => 'wrongpassword',
        ]);

        $response->assertStatus(429)
            ->assertJsonPath('success', false)
            ->assertJsonStructure(['success', 'message', 'retry_after']);
    }

    /**
     * Test retrieving current user profile and status.
     */
    public function test_user_can_retrieve_current_user_profile(): void
    {
        $user = $this->actingAsRole('receptionist');

        $this->getJson(route('auth.me'))
            ->assertOk()
            ->assertJsonPath('user.id', $user->id);

        $this->getJson(route('auth.status'))
            ->assertOk()
            ->assertJsonPath('authenticated', true)
            ->assertJsonPath('user_id', $user->id);
    }

    /**
     * Test user logout revokes token.
     */
    public function test_user_can_logout_and_revoke_token(): void
    {
        $this->actingAsRole('receptionist');

        $response = $this->postJson(route('auth.logout'));

        $response->assertOk()
            ->assertJsonPath('message', 'Logged out successfully');
    }

    /**
     * Test logging out from all devices.
     */
    public function test_user_can_logout_all_devices(): void
    {
        $this->actingAsRole('receptionist');

        $response = $this->postJson(route('auth.logout-all'));

        $response->assertOk()
            ->assertJsonPath('message', 'Logged out from all devices');
    }

    /**
     * Test token refresh.
     */
    public function test_user_can_refresh_token(): void
    {
        $this->actingAsRole('receptionist');

        $response = $this->postJson(route('auth.refresh-token'));

        $response->assertOk()
            ->assertJsonPath('message', 'Token refreshed successfully')
            ->assertJsonStructure(['token']);
    }

    /**
     * Test password change with strong password satisfying complexity rules.
     */
    public function test_user_can_change_password(): void
    {
        $user = User::factory()->create([
            'password' => Hash::make('OldPassword1!'),
        ]);
        $this->actingAs($user, 'sanctum');

        $response = $this->postJson(route('auth.change-password'), [
            'current_password'          => 'OldPassword1!',
            'new_password'              => 'NewPassword2@',
            'new_password_confirmation' => 'NewPassword2@',
        ]);

        $response->assertOk()
            ->assertJsonPath('message', 'Password changed successfully');

        $user->refresh();
        $this->assertTrue(Hash::check('NewPassword2@', $user->password));
    }

    /**
     * Test that a weak password is rejected on change-password.
     */
    public function test_weak_password_is_rejected_on_change_password(): void
    {
        $user = User::factory()->create([
            'password' => Hash::make('OldPassword1!'),
        ]);
        $this->actingAs($user, 'sanctum');

        // Weak password: no uppercase, no symbol, too simple
        $response = $this->postJson(route('auth.change-password'), [
            'current_password'          => 'OldPassword1!',
            'new_password'              => 'simple123',
            'new_password_confirmation' => 'simple123',
        ]);

        $response->assertStatus(422)
            ->assertJsonPath('success', false)
            ->assertJsonStructure(['success', 'message', 'errors']);
    }
}

