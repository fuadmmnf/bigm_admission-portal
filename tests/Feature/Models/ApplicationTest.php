<?php

namespace Tests\Feature\Models;

use App\Models\Application;
use App\Models\Exam;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ApplicationTest extends TestCase
{
    use RefreshDatabase;

    public function test_application_can_be_created_with_exam_and_applicant_details(): void
    {
        $exam = Exam::factory()->create();

        $application = Application::factory()->create([
            'exam_id' => $exam->id,
            'applicant_name' => 'John Doe',
            'applicant_email' => 'john@example.com',
            'applicant_phone' => '01700000000',
            'status' => 'submitted',
        ]);

        $this->assertDatabaseHas('applications', [
            'exam_id' => $exam->id,
            'applicant_name' => 'John Doe',
            'applicant_email' => 'john@example.com',
            'status' => 'submitted',
        ]);
    }

    public function test_application_has_ulid_public_identifier(): void
    {
        $application = Application::factory()->create();

        $this->assertNotNull($application->ulid);
        $this->assertMatchesRegularExpression('/^[0-9A-HJKMNP-TV-Z]{26}$/', $application->ulid);
    }

    public function test_application_belongs_to_exam(): void
    {
        $exam = Exam::factory()->create();
        $application = Application::factory()->create(['exam_id' => $exam->id]);

        $this->assertTrue($application->exam()->exists());
        $this->assertEquals($exam->id, $application->exam->id);
    }
}

