<?php

namespace Tests\Feature\Models;

use App\Models\Category;
use App\Models\Exam;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ExamTest extends TestCase
{
    use RefreshDatabase;

    public function test_exam_can_be_created_with_required_fields(): void
    {
        $category = Category::factory()->create(['type' => 'exam']);

        $exam = Exam::factory()->create([
            'category_id' => $category->id,
            'name' => 'SSC Admission 2026',
            'status' => 'active',
        ]);

        $this->assertDatabaseHas('exams', [
            'name' => 'SSC Admission 2026',
            'status' => 'active',
        ]);
        $this->assertEquals($category->id, $exam->category_id);
    }

    public function test_exam_has_ulid_public_identifier(): void
    {
        $exam = Exam::factory()->create();

        $this->assertNotNull($exam->ulid);
        $this->assertMatchesRegularExpression('/^[0-9A-HJKMNP-TV-Z]{26}$/', $exam->ulid);
    }

    public function test_exam_belongs_to_category(): void
    {
        $category = Category::factory()->create(['type' => 'exam']);
        $exam = Exam::factory()->create(['category_id' => $category->id]);

        $this->assertTrue($exam->category()->exists());
        $this->assertEquals($category->id, $exam->category->id);
    }

    public function test_exam_has_applications(): void
    {
        $exam = Exam::factory()->create();

        $this->assertTrue($exam->applications()->exists());
    }
}

