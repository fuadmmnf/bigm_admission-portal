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

        Application::factory()->create([
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

    public function test_google_form_payload_maps_searchable_fields_and_stores_other_values_in_additional_info(): void
    {
        $attributes = Application::fromGoogleFormPayload([
            "Applicant's Name*" => 'Faria Akter',
            'Email*' => 'faria@example.com',
            'Mobile Number*' => '01711111111',
            'National ID / Birth Registration / Passport Number*' => '1999999999999',
            "Father's Name*" => 'Abdur Rahman',
            'District*' => 'Dhaka',
            'Transaction ID*' => 'TXN-12345',
        ]);

        $this->assertSame('Faria Akter', $attributes['applicant_name']);
        $this->assertSame('faria@example.com', $attributes['applicant_email']);
        $this->assertSame('01711111111', $attributes['applicant_phone']);
        $this->assertSame('1999999999999', $attributes['applicant_id_number']);

        $this->assertArrayHasKey('additional_info', $attributes);
        $this->assertSame('Abdur Rahman', $attributes['additional_info']['father_s_name']);
        $this->assertSame('Dhaka', $attributes['additional_info']['district']);
        $this->assertSame('TXN-12345', $attributes['additional_info']['transaction_id']);
    }

    public function test_google_form_payload_merges_existing_additional_info(): void
    {
        $attributes = Application::fromGoogleFormPayload([
            'Post Office*' => 'Tejgaon',
        ], [
            'status' => 'submitted',
            'additional_info' => [
                'present_address' => 'Dhaka',
            ],
        ]);

        $this->assertSame('submitted', $attributes['status']);
        $this->assertSame('Dhaka', $attributes['additional_info']['present_address']);
        $this->assertSame('Tejgaon', $attributes['additional_info']['post_office']);
    }
}
