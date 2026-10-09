<?php

namespace Tests\Feature\Public;

use App\Models\Application;
use App\Models\Exam;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Str;
use Tests\TestCase;

class DocumentDownloadTest extends TestCase
{
    use RefreshDatabase;

    protected Exam $activeExam;
    protected Exam $inactiveExam;
    protected Application $application;

    protected function setUp(): void
    {
        parent::setUp();

        // Create active and inactive exams
        $this->activeExam = Exam::factory()->create([
            'status' => 'active',
            'name' => 'Active Exam',
        ]);

        $this->inactiveExam = Exam::factory()->create([
            'status' => 'draft',
            'name' => 'Inactive Exam',
        ]);

        // Create an application
        $this->application = Application::factory()->create([
            'exam_id' => $this->activeExam->id,
            'applicant_email' => 'test@example.com',
            'applicant_nid' => '1234567890123',
            'applicant_name' => 'Test Applicant',
            'additional_info' => [
                'personal' => [
                    'date_of_birth' => '1990-05-15',
                ],
            ],
        ]);
    }

    public function test_can_view_public_download_form(): void
    {
        $response = $this->get(route('documents.form'));

        $response->assertStatus(200);
        $response->assertViewIs('pages.public-document-download');
        $response->assertViewHas('exams');
    }

    public function test_form_only_shows_active_exams(): void
    {
        $response = $this->get(route('documents.form'));

        $exams = $response->viewData('exams');
        $this->assertCount(1, $exams);
        $this->assertEquals($this->activeExam->id, $exams[0]->id);
    }

    public function test_can_search_application_with_valid_credentials(): void
    {
        $response = $this->postJson(route('documents.search'), [
            'exam_id' => $this->activeExam->id,
            'email' => 'test@example.com',
            'date_of_birth' => '1990-05-15',
        ]);

        $response->assertStatus(200);
        $response->assertJsonPath('found', true);
        $response->assertJsonPath('application.applicant_name', 'Test Applicant');
    }

    public function test_search_fails_with_invalid_email(): void
    {
        $response = $this->postJson(route('documents.search'), [
            'exam_id' => $this->activeExam->id,
            'email' => 'wrong@example.com',
            'date_of_birth' => '1990-05-15',
        ]);

        $response->assertStatus(404);
        $response->assertJsonPath('found', false);
    }

    public function test_search_fails_with_invalid_date_of_birth(): void
    {
        $response = $this->postJson(route('documents.search'), [
            'exam_id' => $this->activeExam->id,
            'email' => 'test@example.com',
            'date_of_birth' => '1999-12-31',
        ]);

        $response->assertStatus(404);
        $response->assertJsonPath('found', false);
    }

    public function test_search_requires_valid_exam_id(): void
    {
        $response = $this->postJson(route('documents.search'), [
            'exam_id' => Str::ulid(),
            'email' => 'test@example.com',
            'date_of_birth' => '1990-05-15',
        ]);

        $response->assertStatus(422);
        $response->assertJsonValidationErrors('exam_id');
    }

    public function test_search_requires_valid_email(): void
    {
        $response = $this->postJson(route('documents.search'), [
            'exam_id' => $this->activeExam->id,
            'email' => 'not-an-email',
            'date_of_birth' => '1990-05-15',
        ]);

        $response->assertStatus(422);
        $response->assertJsonValidationErrors('email');
    }

    public function test_search_requires_date_of_birth(): void
    {
        $response = $this->postJson(route('documents.search'), [
            'exam_id' => $this->activeExam->id,
            'email' => 'test@example.com',
            'date_of_birth' => '',
        ]);

        $response->assertStatus(422);
        $response->assertJsonValidationErrors('date_of_birth');
    }

    public function test_download_cv_requires_valid_credentials(): void
    {
        $response = $this->post(route('documents.cv'), [
            'exam_id' => $this->activeExam->id,
            'email' => 'test@example.com',
            'date_of_birth' => '1990-05-15',
        ]);

        // Should generate PDF or redirect
        $this->assertIn($response->status(), [200, 302]);
    }

    public function test_download_admit_card_requires_valid_credentials(): void
    {
        $response = $this->post(route('documents.admit-card'), [
            'exam_id' => $this->activeExam->id,
            'email' => 'test@example.com',
            'date_of_birth' => '1990-05-15',
        ]);

        // Should generate PDF or redirect
        $this->assertIn($response->status(), [200, 302]);
    }

    public function test_download_cv_fails_with_invalid_credentials(): void
    {
        $response = $this->post(route('documents.cv'), [
            'exam_id' => $this->activeExam->id,
            'email' => 'wrong@example.com',
            'date_of_birth' => '1990-05-15',
        ]);

        $response->assertStatus(404);
    }

    public function test_download_admit_card_fails_with_invalid_credentials(): void
    {
        $response = $this->post(route('documents.admit-card'), [
            'exam_id' => $this->activeExam->id,
            'email' => 'test@example.com',
            'date_of_birth' => '1999-12-31',
        ]);

        $response->assertStatus(404);
    }
}
