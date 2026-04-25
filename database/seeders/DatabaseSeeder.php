<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        $this->call([
            RolesSeeder::class,
            AuthUsersSeeder::class,
            ProgramCategoriesSeeder::class,
            BangladeshLocationCategoriesSeeder::class,
            ExamPaginationSeeder::class,
            ExamApplicantsSeeder::class,
        ]);
    }
}
