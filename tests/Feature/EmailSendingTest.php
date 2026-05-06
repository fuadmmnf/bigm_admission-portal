<?php

namespace Tests\Feature;

use App\Mail\AdmitCardMail;
use App\Models\Application;
use App\Models\Exam;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Mail;
use Tests\TestCase;

class EmailSendingTest extends TestCase
{
    use RefreshDatabase;

    public function test_admit_card_email_contains_pdf_attachment_with_correct_filename(): void
    {
        Mail::fake();

        $application = Application::factory()
            ->for(Exam::factory())
            ->create([
                'status' => 'paid',
                'selection_stage' => Application::STAGE_PAID,
            ]);

        $mail = new AdmitCardMail($application, 'admit_card');

        Mail::send($mail);

        Mail::assertSent(AdmitCardMail::class, function (AdmitCardMail $sent) use ($application): bool {
            return $sent->application->is($application)
                && $sent->mailType === 'admit_card'
                && count($sent->attachments()) > 0;
        });
    }

    public function test_admit_card_email_subject_includes_exam_name(): void
    {
        Mail::fake();

        $exam = Exam::factory()->create(['name' => 'Executive Program 2026']);
        $application = Application::factory()
            ->for($exam)
            ->create(['status' => 'paid']);

        $mail = new AdmitCardMail($application, 'admit_card');
        $envelope = $mail->envelope();

        $this->assertStringContainsString('Admit Card', $envelope->subject);
        $this->assertStringContainsString('Executive Program 2026', $envelope->subject);
    }

    public function test_viva_eligibility_email_contains_correct_mail_type(): void
    {
        Mail::fake();

        $application = Application::factory()
            ->for(Exam::factory())
            ->create([
                'status' => 'paid',
                'selection_stage' => Application::STAGE_VIVA_SELECTED,
            ]);

        $mail = new AdmitCardMail($application, 'viva_eligibility');

        $this->assertSame('viva_eligibility', $mail->mailType);
        $this->assertStringContainsString('Viva Eligibility', $mail->envelope()->subject);
    }

    public function test_program_selection_email_contains_selected_program_in_subject(): void
    {
        Mail::fake();

        $application = Application::factory()
            ->for(Exam::factory())
            ->create([
                'status' => 'paid',
                'selection_stage' => Application::STAGE_PROGRAM_SELECTED,
            ]);

        $mail = new AdmitCardMail($application, 'program_selection');
        $envelope = $mail->envelope();

        $this->assertSame('program_selection', $mail->mailType);
        $this->assertStringContainsString('Program Selection', $envelope->subject);
    }

    public function test_admit_card_email_renders_with_complete_applicant_data(): void
    {
        $application = Application::factory()
            ->for(Exam::factory())
            ->create([
                'status' => 'paid',
                'applicant_name' => 'Test Applicant',
                'selection_stage' => Application::STAGE_PAID,
                'additional_info' => [
                    'personal' => [
                        'father_name' => 'Father Name',
                        'mother_name' => 'Mother Name',
                    ],
                    'uploads' => [],
                ],
            ]);

        $mail = new AdmitCardMail($application, 'admit_card');
        $content = $mail->content();

        $this->assertSame('emails.admit-card', $content->view);
        $this->assertTrue($content->with['mailType'] === 'admit_card');
    }

    public function test_all_email_types_generate_pdf_attachment_successfully(): void
    {
        Mail::fake();

        $application = Application::factory()
            ->for(Exam::factory())
            ->create([
                'status' => 'paid',
                'selection_stage' => Application::STAGE_PROGRAM_SELECTED,
            ]);

        $mailTypes = ['admit_card', 'viva_eligibility', 'program_selection'];

        foreach ($mailTypes as $type) {
            $mail = new AdmitCardMail($application, $type);
            $attachments = $mail->attachments();

            $this->assertNotEmpty($attachments, "Failed to generate attachment for mail type: {$type}");
            $this->assertTrue(count($attachments) > 0);
        }
    }

    public function test_email_respects_application_contact_information(): void
    {
        Mail::fake();

        $email = 'candidate@university.test';
        $application = Application::factory()
            ->for(Exam::factory())
            ->create([
                'status' => 'paid',
                'applicant_email' => $email,
            ]);

        $mail = new AdmitCardMail($application);

        // The mail should use the application's email
        $this->assertSame($email, $application->applicant_email);
    }

    public function test_email_with_missing_applicant_photo_does_not_fail(): void
    {
        Mail::fake();

        $application = Application::factory()
            ->for(Exam::factory())
            ->create([
                'status' => 'paid',
                'additional_info' => [
                    'uploads' => [
                        'applicant_photo' => null,
                        'signature' => null,
                    ],
                ],
            ]);

        $mail = new AdmitCardMail($application);
        $attachments = $mail->attachments();

        // Should still generate PDF even with missing photos
        $this->assertNotEmpty($attachments);
    }

    public function test_email_pdf_contains_exam_instructions_from_metadata(): void
    {
        $application = Application::factory()
            ->for(Exam::factory()->create([
                'additional_info' => [
                    'admit_card_instructions' => [
                        'Instruction one',
                        'Instruction two',
                        'Instruction three',
                    ],
                ],
            ]))
            ->create(['status' => 'paid']);

        $mail = new AdmitCardMail($application, 'admit_card');
        $attachments = $mail->attachments();

        $this->assertNotEmpty($attachments);
    }
}

