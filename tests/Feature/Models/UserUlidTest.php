<?php

namespace Tests\Feature\Models;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class UserUlidTest extends TestCase
{
    use RefreshDatabase;

    public function test_user_receives_ulid_on_creation(): void
    {
        $user = User::factory()->create();

        $this->assertNotNull($user->ulid);
        $this->assertMatchesRegularExpression('/^[0-9A-HJKMNP-TV-Z]{26}$/', $user->ulid);
    }
}

