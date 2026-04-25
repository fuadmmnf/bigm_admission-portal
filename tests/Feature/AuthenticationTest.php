<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class AuthenticationTest extends TestCase
{
    use RefreshDatabase;

    public function test_login_screen_can_be_rendered(): void
    {
        $response = $this->get('/login');

        $response->assertStatus(200);
    }

    public function test_admin_users_can_authenticate_using_the_login_screen(): void
    {
        $user = User::factory()->create();
        Role::findOrCreate('admin', 'web');
        $user->assignRole('admin');

        $response = $this->post('/login', [
            'email' => $user->email,
            'password' => 'password',
        ]);

        $this->assertAuthenticated();
        $response->assertRedirect(route('dashboard', absolute: false));
    }

    public function test_moderator_users_can_authenticate_using_the_login_screen(): void
    {
        $user = User::factory()->create();
        Role::findOrCreate('moderator', 'web');
        $user->assignRole('moderator');

        $response = $this->post('/login', [
            'email' => $user->email,
            'password' => 'password',
        ]);

        $this->assertAuthenticated();
        $response->assertRedirect(route('dashboard', absolute: false));
    }

    public function test_non_privileged_users_can_not_authenticate(): void
    {
        $user = User::factory()->create();

        $this->post('/login', [
            'email' => $user->email,
            'password' => 'password',
        ]);

        $this->assertGuest();
    }

    public function test_privileged_users_can_not_authenticate_with_invalid_password(): void
    {
        $user = User::factory()->create();
        Role::findOrCreate('admin', 'web');
        $user->assignRole('admin');

        $this->post('/login', [
            'email' => $user->email,
            'password' => 'wrong-password',
        ]);

        $this->assertGuest();
    }
}
