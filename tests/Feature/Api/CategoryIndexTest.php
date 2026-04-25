<?php

namespace Tests\Feature\Api;

use App\Models\Category;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CategoryIndexTest extends TestCase
{
    use RefreshDatabase;

    public function test_categories_can_be_filtered_by_type(): void
    {
        Category::factory()->create([
            'name' => 'Exam 2026',
            'type' => 'exam',
        ]);
        Category::factory()->create([
            'name' => 'Dhaka',
            'type' => 'location',
        ]);

        $response = $this->getJson('/api/categories?type=exam');

        $response
            ->assertOk()
            ->assertJsonCount(1, 'data')
            ->assertJsonPath('data.0.type', 'exam')
            ->assertJsonPath('data.0.name', 'Exam 2026');
    }

    public function test_categories_can_be_filtered_by_parent_ulid(): void
    {
        $examRootA = Category::factory()->create([
            'name' => 'SSC',
            'type' => 'exam',
        ]);

        $examRootB = Category::factory()->create([
            'name' => 'HSC',
            'type' => 'exam',
        ]);

        Category::factory()->create([
            'name' => 'SSC 2026',
            'type' => 'exam',
            'parent_id' => $examRootA->id,
        ]);

        Category::factory()->create([
            'name' => 'HSC 2026',
            'type' => 'exam',
            'parent_id' => $examRootB->id,
        ]);

        $response = $this->getJson('/api/categories?type=exam&parent_ulid='.$examRootA->ulid);

        $response
            ->assertOk()
            ->assertJsonCount(1, 'data')
            ->assertJsonPath('data.0.name', 'SSC 2026')
            ->assertJsonPath('data.0.parent_id', $examRootA->ulid);
    }

    public function test_category_response_uses_ulid_public_identifier(): void
    {
        Category::factory()->create([
            'name' => 'Exam 2027',
            'type' => 'exam',
        ]);

        $response = $this->getJson('/api/categories?type=exam');

        $publicId = data_get($response->json(), 'data.0.id');

        $this->assertIsString($publicId);
        $this->assertMatchesRegularExpression('/^[0-9A-HJKMNP-TV-Z]{26}$/', $publicId);
        $response->assertJsonMissingPath('data.0.ulid');
    }
}

