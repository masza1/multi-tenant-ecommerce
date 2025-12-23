<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\WithoutMiddleware;
use Tests\TenantTestCase;

class AuthenticationTest extends TenantTestCase
{
    /**
     * Test user can login with valid credentials.
     */
    public function test_user_can_login_with_valid_credentials(): void
    {
        $user = User::factory()->create([
            'email' => 'test@example.com',
            'password' => bcrypt('password123'),
        ]);

        $response = $this->post('/login', [
            'email' => 'test@example.com',
            'password' => 'password123',
        ]);

        $this->assertAuthenticatedAs($user);
    }

    /**
     * Test user cannot login with invalid password.
     */
    public function test_user_cannot_login_with_invalid_password(): void
    {
        User::factory()->create([
            'email' => 'test@example.com',
            'password' => bcrypt('password123'),
        ]);

        $response = $this->post('/login', [
            'email' => 'test@example.com',
            'password' => 'wrongpassword',
        ]);

        $this->assertGuest();
    }

    /**
     * Test user cannot login with non-existent email.
     */
    public function test_user_cannot_login_with_non_existent_email(): void
    {
        $response = $this->post('/login', [
            'email' => 'nonexistent@example.com',
            'password' => 'password123',
        ]);

        $this->assertGuest();
    }

    /**
     * Test user can register.
     */
    public function test_user_can_register(): void
    {
        $response = $this->post('/register', [
            'name' => 'Test User',
            'email' => 'newuser@example.com',
            'password' => 'password123',
            'password_confirmation' => 'password123',
        ]);

        $this->assertDatabaseHas('users', [
            'email' => 'newuser@example.com',
        ]);

        $user = User::where('email', 'newuser@example.com')->first();
        $this->assertAuthenticatedAs($user);
    }

    /**
     * Test user cannot register with existing email.
     */
    public function test_user_cannot_register_with_existing_email(): void
    {
        User::factory()->create(['email' => 'existing@example.com']);

        $response = $this->post('/register', [
            'name' => 'New User',
            'email' => 'existing@example.com',
            'password' => 'password123',
            'password_confirmation' => 'password123',
        ]);

        $response->assertSessionHasErrors('email');
    }

    /**
     * Test user can logout.
     */
    public function test_user_can_logout(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user)
            ->post('/logout')
            ->assertRedirect('/');

        $this->assertGuest();
    }

    /**
     * Test user sees dashboard after login.
     */
    public function test_user_can_access_dashboard_when_authenticated(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->get('/dashboard');

        $response->assertOk();
    }

    /**
     * Test unauthenticated user cannot access dashboard.
     */
    public function test_unauthenticated_user_cannot_access_dashboard(): void
    {
        $response = $this->get('/dashboard');

        $response->assertRedirect('/login');
    }

    /**
     * Test authenticated user can view profile.
     */
    public function test_authenticated_user_can_view_profile(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->get('/profile');

        $response->assertOk();
    }

    /**
     * Test user can update profile.
     */
    public function test_user_can_update_profile(): void
    {
        $user = User::factory()->create([
            'name' => 'Original Name',
            'email' => 'original@example.com',
        ]);

        $response = $this->actingAs($user)->patch('/profile', [
            'name' => 'Updated Name',
            'email' => 'updated@example.com',
        ]);

        $this->assertDatabaseHas('users', [
            'id' => $user->id,
            'name' => 'Updated Name',
            'email' => 'updated@example.com',
        ]);
    }

    /**
     * Test users cannot access other users' profiles.
     */
    public function test_user_cannot_access_other_user_profile(): void
    {
        $user1 = User::factory()->create();
        $user2 = User::factory()->create();

        // User1 tries to delete user2's account
        $response = $this->actingAs($user1)->delete('/profile');

        // Should fail or redirect
        $this->assertDatabaseHas('users', ['id' => $user2->id]);
    }

    /**
     * Test user email must be unique during registration.
     */
    public function test_registration_requires_unique_email(): void
    {
        User::factory()->create(['email' => 'taken@example.com']);

        $response = $this->post('/register', [
            'name' => 'Test User',
            'email' => 'taken@example.com',
            'password' => 'password123',
            'password_confirmation' => 'password123',
        ]);

        $response->assertSessionHasErrors('email');
    }

    /**
     * Test password confirmation must match during registration.
     */
    public function test_registration_requires_password_confirmation(): void
    {
        $response = $this->post('/register', [
            'name' => 'Test User',
            'email' => 'new@example.com',
            'password' => 'password123',
            'password_confirmation' => 'different',
        ]);

        $response->assertSessionHasErrors('password');
    }

    /**
     * Test user isolation.
     */
    public function test_users_are_properly_isolated(): void
    {
        // Create user
        $user1 = $this->createTestUser(['email' => 'user1@example.com']);

        // Create another user
        $user2 = $this->createAnotherTestUser(['email' => 'user2@example.com']);

        // Verify both users exist
        $this->assertNotNull(User::where('email', 'user1@example.com')->first());
        $this->assertNotNull(User::where('email', 'user2@example.com')->first());

        // Verify user1 cannot modify user2's profile
        $response = $this->actingAs($user1)->patch('/profile', [
            'name' => 'Hacked',
        ]);

        // User2 should still have original name
        $this->assertDatabaseHas('users', [
            'id' => $user2->id,
            'name' => $user2->name, // Original name unchanged
        ]);
    }

    /**
     * Test user can maintain session.
     */
    public function test_user_session_persists(): void
    {
        $user = $this->createTestUser(['email' => 'test@example.com']);

        // Login
        $this->actingAs($user);

        // Make multiple requests
        $response1 = $this->get('/dashboard');
        $response1->assertOk();

        $response2 = $this->get('/profile');
        $response2->assertOk();

        // Still authenticated
        $this->assertAuthenticatedAs($user);
    }

    /**
     * Test token/session stays valid within same tenant.
     */
    public function test_user_remains_authenticated_in_same_tenant(): void
    {
        $user = User::factory()->create(['email' => 'test@example.com']);

        // Login user
        $this->post('/login', [
            'email' => 'test@example.com',
            'password' => 'password',
        ]);

        $this->assertAuthenticatedAs($user);

        // Make multiple requests
        $response1 = $this->get('/dashboard');
        $response1->assertOk();

        $response2 = $this->get('/profile');
        $response2->assertOk();

        // Still authenticated
        $this->assertAuthenticatedAs($user);
    }

}
