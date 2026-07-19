<?php

namespace Tests\Feature\Api\V1\Auth;

use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Tests\ApiTestCase;

class AuthTest extends ApiTestCase
{
    /**
     * Test successful login with valid credentials.
     */
    public function test_user_can_login_with_valid_credentials(): void
    {
        $user = User::factory()->create([
            'email' => 'auth.test@example.com',
            'password' => Hash::make('password123'),
            'is_active' => true,
        ]);

        $response = $this->postJson(route('auth.login'), [
            'email' => 'auth.test@example.com',
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
            'email' => 'bad.auth@example.com',
            'password' => Hash::make('password123'),
        ]);

        $response = $this->postJson(route('auth.login'), [
            'email' => 'bad.auth@example.com',
            'password' => 'wrongpassword',
        ]);

        $response->assertStatus(401)
            ->assertJsonPath('message', 'Invalid credentials');
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
        $user = $this->actingAsRole('receptionist');

        $response = $this->postJson(route('auth.logout'));

        $response->assertOk()
            ->assertJsonPath('message', 'Logged out successfully');
    }

    /**
     * Test logging out from all devices.
     */
    public function test_user_can_logout_all_devices(): void
    {
        $user = $this->actingAsRole('receptionist');

        $response = $this->postJson(route('auth.logout-all'));

        $response->assertOk()
            ->assertJsonPath('message', 'Logged out from all devices');
    }

    /**
     * Test token refresh.
     */
    public function test_user_can_refresh_token(): void
    {
        $user = $this->actingAsRole('receptionist');

        $response = $this->postJson(route('auth.refresh-token'));

        $response->assertOk()
            ->assertJsonPath('message', 'Token refreshed successfully')
            ->assertJsonStructure(['token']);
    }

    /**
     * Test password change and reuse protections.
     */
    public function test_user_can_change_password(): void
    {
        $user = User::factory()->create([
            'password' => Hash::make('oldpassword123'),
        ]);
        $this->actingAs($user, 'sanctum');

        $response = $this->postJson(route('auth.change-password'), [
            'current_password' => 'oldpassword123',
            'new_password' => 'newpassword123',
            'new_password_confirmation' => 'newpassword123',
        ]);

        $response->assertOk()
            ->assertJsonPath('message', 'Password changed successfully');

        $user->refresh();
        $this->assertTrue(Hash::check('newpassword123', $user->password));
    }
}
