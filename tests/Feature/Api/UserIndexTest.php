<?php

namespace Tests\Feature\Api;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use Spatie\Permission\Models\Permission;
use Tests\TestCase;

class UserIndexTest extends TestCase
{
    use RefreshDatabase;

    public function test_users_index_requires_authentication(): void
    {
        $response = $this->getJson('/api/users');

        $response->assertUnauthorized();
    }

    public function test_users_index_returns_paginated_users_for_authorized_user(): void
    {
        Permission::findOrCreate('users.view', 'web');

        $authorizedUser = User::factory()->create([
            'name' => 'Access User',
            'email' => 'access@example.com',
        ]);
        $authorizedUser->givePermissionTo('users.view');

        User::factory()->create([
            'name' => 'Alice Smith',
            'email' => 'alice@example.com',
        ]);
        User::factory()->create([
            'name' => 'Bob Stone',
            'email' => 'bob@example.com',
        ]);

        Sanctum::actingAs($authorizedUser);

        $response = $this->getJson('/api/users?filter[name]=Alice&sort=name&per_page=5');

        $firstId = data_get($response->json(), 'data.0.id');

        $response
            ->assertOk()
            ->assertJsonPath('meta.per_page', 5)
            ->assertJsonCount(1, 'data')
            ->assertJsonPath('data.0.name', 'Alice Smith');

        $this->assertIsString($firstId);
        $this->assertMatchesRegularExpression('/^[0-9A-HJKMNP-TV-Z]{26}$/', $firstId);
    }
}


