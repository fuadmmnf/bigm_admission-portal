<?php

namespace Tests\Feature\Database;

use App\Models\Category;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CategorySeedersTest extends TestCase
{
    use RefreshDatabase;

    public function test_database_seeder_creates_six_program_categories_from_the_previous_google_form(): void
    {
        $this->seed();

        $programs = Category::query()
            ->where('type', 'program')
            ->orderBy('name')
            ->get();

        $this->assertCount(6, $programs);

        $this->assertSame(
            [
                'Governance and Public Policy',
                'Human Resource Management',
                'International Economic Relations',
                'Procurement and Supply Chain Management',
                'Project Management',
                'Public Private Financial Management',
            ],
            $programs->pluck('name')->all()
        );

        $this->assertDatabaseHas('categories', [
            'type' => 'program',
            'name' => 'Human Resource Management',
        ]);
    }

    public function test_database_seeder_creates_bangladesh_location_hierarchy_with_correct_parent_relations(): void
    {
        $this->seed();

        $this->assertSame(8, Category::query()->where('type', 'division')->count());
        $this->assertSame(64, Category::query()->where('type', 'district')->count());
        $this->assertSame(495, Category::query()->where('type', 'upazila')->count());

        $chattogram = Category::query()
            ->where('type', 'division')
            ->where('name', 'Chattogram')
            ->firstOrFail();

        $cumilla = Category::query()
            ->where('type', 'district')
            ->where('name', 'Cumilla')
            ->firstOrFail();

        $debidwar = Category::query()
            ->where('type', 'upazila')
            ->where('name', 'Debidwar')
            ->firstOrFail();

        $this->assertSame($chattogram->id, $cumilla->parent_id);
        $this->assertSame($cumilla->id, $debidwar->parent_id);
    }

    public function test_location_seeders_store_legacy_names_for_form_compatibility(): void
    {
        $this->seed();

        $dhakaDivision = Category::query()
            ->where('type', 'division')
            ->where('name', 'Dhaka')
            ->firstOrFail();

        $cumillaDistrict = Category::query()
            ->where('type', 'district')
            ->where('name', 'Cumilla')
            ->firstOrFail();

        $this->assertNull(data_get($dhakaDivision->additional_info, 'legacy_name'));
        $this->assertSame('Comilla', data_get($cumillaDistrict->additional_info, 'legacy_name'));
        $this->assertSame('কুমিল্লা', data_get($cumillaDistrict->additional_info, 'bn_name'));
    }
}

