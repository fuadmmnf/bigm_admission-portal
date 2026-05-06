<?php

namespace Tests\Feature\Database;

use Database\Seeders\DatabaseSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class DatabaseSeederPublicFilesTest extends TestCase
{
    use RefreshDatabase;

    public function test_database_seeder_cleans_up_old_seeded_files_and_keeps_non_seeded_files(): void
    {
        Storage::fake('public');

        Storage::disk('public')->put('seeded_uploads/photos/old-photo.png', 'legacy-photo', 'public');
        Storage::disk('public')->put('seeded_uploads/signatures/old-signature.png', 'legacy-signature', 'public');
        Storage::disk('public')->put('exams/brochures/seeded-active-old.pdf', 'legacy-seeded-brochure', 'public');
        Storage::disk('public')->put('exams/circulars/seeded-active-old.pdf', 'legacy-seeded-circular', 'public');

        Storage::disk('public')->put('exams/brochures/custom-brochure.pdf', 'keep-me', 'public');
        Storage::disk('public')->put('exams/circulars/custom-circular.pdf', 'keep-me-too', 'public');

        $this->seed(DatabaseSeeder::class);

        Storage::disk('public')->assertMissing('seeded_uploads/photos/old-photo.png');
        Storage::disk('public')->assertMissing('seeded_uploads/signatures/old-signature.png');
        Storage::disk('public')->assertMissing('exams/brochures/seeded-active-old.pdf');
        Storage::disk('public')->assertMissing('exams/circulars/seeded-active-old.pdf');

        Storage::disk('public')->assertExists('exams/brochures/custom-brochure.pdf');
        Storage::disk('public')->assertExists('exams/circulars/custom-circular.pdf');

        $this->assertNotEmpty(Storage::disk('public')->allFiles('seeded_uploads/photos'));
        $this->assertNotEmpty(Storage::disk('public')->allFiles('seeded_uploads/signatures'));
    }

    public function test_database_seeder_writes_seeded_media_with_public_visibility(): void
    {
        Storage::fake('public');

        $this->seed(DatabaseSeeder::class);

        $seededPaths = [
            Storage::disk('public')->allFiles('seeded_uploads/photos')[0] ?? null,
            Storage::disk('public')->allFiles('seeded_uploads/signatures')[0] ?? null,
            collect(Storage::disk('public')->allFiles('exams/brochures'))
                ->first(fn (string $path): bool => str_starts_with(basename($path), 'seeded-active-')),
            collect(Storage::disk('public')->allFiles('exams/circulars'))
                ->first(fn (string $path): bool => str_starts_with(basename($path), 'seeded-active-')),
        ];

        foreach ($seededPaths as $path) {
            $this->assertNotNull($path, 'Expected a seeded file but none was found.');
            $this->assertSame('public', Storage::disk('public')->getVisibility($path));
        }
    }
}

