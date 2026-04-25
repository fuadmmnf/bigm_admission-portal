<?php

namespace Database\Seeders;

use App\Models\Category;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class ProgramCategoriesSeeder extends Seeder
{
    public function run(): void
    {
        $programs = require database_path('seeders/data/programs.php');

        foreach ($programs as $program) {
            $category = Category::query()->firstOrNew([
                'type' => 'program',
                'name' => $program['name'],
            ]);

            if (blank($category->ulid)) {
                $category->ulid = (string) Str::ulid();
            }

            $category->additional_info = [
                'code' => $program['code'],
                'legacy_name' => $program['legacy_name'],
                'source' => $program['source'],
            ];

            $category->save();
        }
    }
}

