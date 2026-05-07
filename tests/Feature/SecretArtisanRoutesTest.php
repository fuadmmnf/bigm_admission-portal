<?php

namespace Tests\Feature;

use Illuminate\Support\Facades\Artisan;
use Tests\TestCase;

class SecretArtisanRoutesTest extends TestCase
{
    public function test_optimize_route_returns_404_for_wrong_secret(): void
    {
        config()->set('secret_artisan.secret', 'ops-secret');

        $this->get('/_secret/ops/wrong-secret/optimize')->assertNotFound();
    }

    public function test_optimize_route_executes_command_with_valid_secret(): void
    {
        config()->set('secret_artisan.secret', 'ops-secret');

        Artisan::shouldReceive('call')
            ->once()
            ->with('optimize')
            ->andReturn(0);

        Artisan::shouldReceive('output')
            ->once()
            ->andReturn('Optimization completed');

        $this->get('/_secret/ops/ops-secret/optimize')
            ->assertOk()
            ->assertJsonPath('ok', true)
            ->assertJsonPath('command', 'optimize');
    }

    public function test_super_secret_routes_require_same_shared_secret(): void
    {
        config()->set('secret_artisan.secret', 'ops-secret');

        $this->get('/_secret/super/wrong/migrate')->assertNotFound();
        $this->get('/_secret/super/wrong/db-seed')->assertNotFound();
    }

    public function test_migrate_route_executes_with_force_flag(): void
    {
        config()->set('secret_artisan.secret', 'ops-secret');

        Artisan::shouldReceive('call')
            ->once()
            ->with('migrate', ['--force' => true])
            ->andReturn(0);

        Artisan::shouldReceive('output')
            ->once()
            ->andReturn('Migrated');

        $this->get('/_secret/super/ops-secret/migrate')
            ->assertOk()
            ->assertJsonPath('ok', true)
            ->assertJsonPath('command', 'migrate');
    }

    public function test_db_seed_route_executes_with_force_flag(): void
    {
        config()->set('secret_artisan.secret', 'ops-secret');

        Artisan::shouldReceive('call')
            ->once()
            ->with('db:seed', ['--force' => true])
            ->andReturn(0);

        Artisan::shouldReceive('output')
            ->once()
            ->andReturn('Seeded');

        $this->get('/_secret/super/ops-secret/db-seed')
            ->assertOk()
            ->assertJsonPath('ok', true)
            ->assertJsonPath('command', 'db:seed');
    }
}


