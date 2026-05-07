<?php

namespace Tests\Feature\Database;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class AuthRoleSeedersTest extends TestCase
{
    use RefreshDatabase;

    public function test_database_seeder_creates_admin_and_moderator_auth_users(): void
    {
        $this->seed();

        $this->assertDatabaseHas('users', ['email' => 'admin@example.com']);
        $this->assertDatabaseHas('users', ['email' => 'moderator@example.com']);
    }

    public function test_database_seeder_creates_only_auth_roles_for_initial_access(): void
    {
        $this->seed();

        $this->assertTrue(Role::where('name', 'admin')->where('guard_name', 'web')->exists());
        $this->assertTrue(Role::where('name', 'moderator')->where('guard_name', 'web')->exists());
        $this->assertFalse(Role::where('name', 'user')->where('guard_name', 'web')->exists());
    }
}

