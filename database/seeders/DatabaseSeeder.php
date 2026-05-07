<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Storage;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        $this->cleanupSeededPublicFiles();

        $this->call([
            RolesSeeder::class,
            AuthUsersSeeder::class,
            ProgramCategoriesSeeder::class,
            BangladeshLocationCategoriesSeeder::class,
            ExamPaginationSeeder::class,
            ExamApplicantsSeeder::class,
        ]);
    }

    private function cleanupSeededPublicFiles(): void
    {
        Storage::disk('public')->deleteDirectory('seeded_uploads/photos');
        Storage::disk('public')->deleteDirectory('seeded_uploads/signatures');

        foreach (Storage::disk('public')->allFiles('exams/brochures') as $path) {
            if (str_starts_with(basename($path), 'seeded-active-')) {
                Storage::disk('public')->delete($path);
            }
        }

        foreach (Storage::disk('public')->allFiles('exams/circulars') as $path) {
            if (str_starts_with(basename($path), 'seeded-active-')) {
                Storage::disk('public')->delete($path);
            }
        }
    }
}
