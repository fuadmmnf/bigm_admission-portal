<?php

namespace Tests\Feature\Models;

use App\Models\Application;
use App\Models\Exam;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ExamTest extends TestCase
{
    use RefreshDatabase;

    public function test_exam_can_be_created_with_required_fields(): void
    {
        $exam = Exam::factory()->create([
            'name' => 'SSC Admission 2026',
            'status' => 'active',
        ]);

        $this->assertDatabaseHas('exams', [
            'name' => 'SSC Admission 2026',
            'status' => 'active',
        ]);
    }

    public function test_exam_has_ulid_public_identifier(): void
    {
        $exam = Exam::factory()->create();

        $this->assertNotNull($exam->ulid);
        $this->assertMatchesRegularExpression('/^[0-9A-HJKMNP-TV-Z]{26}$/', $exam->ulid);
    }


    public function test_exam_has_applications(): void
    {
        $exam = Exam::factory()->create();
        Application::factory()->create(['exam_id' => $exam->id]);

        $this->assertTrue($exam->applications()->exists());
        $this->assertCount(1, $exam->applications);
    }
}

