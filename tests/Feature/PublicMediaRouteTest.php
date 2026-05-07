<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class PublicMediaRouteTest extends TestCase
{
    use RefreshDatabase;

    public function test_it_streams_a_public_file_from_the_public_disk(): void
    {
        Storage::fake('public');

        $path = 'seeded_uploads/photos/example-photo.png';
        Storage::disk('public')->put($path, 'seeded-image-bytes', 'public');

        $response = $this->get(route('public-media.show', ['path' => $path]));

        $response->assertOk();
        $cacheControl = (string) $response->headers->get('cache-control', '');
        $this->assertStringContainsString('public', $cacheControl);
        $this->assertStringContainsString('max-age=3600', $cacheControl);
    }

    public function test_it_returns_not_found_for_missing_files_and_path_traversal_attempts(): void
    {
        Storage::fake('public');

        $this->get(route('public-media.show', ['path' => 'seeded_uploads/photos/missing.png']))
            ->assertNotFound();

        $this->get('/media/public/%2E%2E%2F.env')->assertNotFound();
    }
}


