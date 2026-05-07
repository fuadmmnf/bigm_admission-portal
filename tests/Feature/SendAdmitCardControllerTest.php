<?php

namespace Tests\Feature;

use App\Mail\AdmitCardMail;
use App\Models\Application;
use App\Models\Exam;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Mail;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class SendAdmitCardControllerTest extends TestCase
{
    use RefreshDatabase;

    public function test_dev_dry_run_can_send_to_selected_paid_applicants_without_queuing_real_mail(): void
    {
        Mail::fake();
        config()->set('admit_card_mail.dry_run', true);

        $admin = $this->makeAdmin();
        $exam = Exam::factory()->create(['status' => 'active']);

        $paidSelected = Application::factory()->create([
            'exam_id' => $exam->id,
            'status' => 'paid',
            'applicant_email' => 'selected@example.test',
        ]);

        Application::factory()->create([
            'exam_id' => $exam->id,
            'status' => 'paid',
            'applicant_email' => 'other@example.test',
        ]);

        $response = $this
            ->actingAs($admin)
            ->from(route('admin.exams.show', $exam))
            ->post(route('admin.exams.send-admit-cards', $exam), [
                'send_scope' => 'selected',
                'active_tab' => 'paid',
                'application_ids' => [$paidSelected->ulid],
            ]);

        $response->assertRedirect(route('admin.exams.show', $exam));
        $response->assertSessionHas('status', 'DRY RUN complete for 1 applicant(s). Admit card email was simulated only.');

        Mail::assertNothingQueued();
    }

    public function test_dev_dry_run_can_send_to_all_paid_applicants_without_selection(): void
    {
        Mail::fake();
        config()->set('admit_card_mail.dry_run', true);

        $admin = $this->makeAdmin();
        $exam = Exam::factory()->create(['status' => 'active']);

        Application::factory()->create([
            'exam_id' => $exam->id,
            'status' => 'paid',
            'applicant_email' => 'paid1@example.test',
        ]);
        Application::factory()->create([
            'exam_id' => $exam->id,
            'status' => 'paid',
            'applicant_email' => 'paid2@example.test',
        ]);
        Application::factory()->create([
            'exam_id' => $exam->id,
            'status' => 'pending',
            'applicant_email' => 'pending@example.test',
        ]);

        $response = $this
            ->actingAs($admin)
            ->from(route('admin.exams.show', $exam))
            ->post(route('admin.exams.send-admit-cards', $exam), [
                'send_scope' => 'all_paid',
                'active_tab' => 'paid',
            ]);

        $response->assertRedirect(route('admin.exams.show', $exam));
        $response->assertSessionHas('status', 'DRY RUN complete for 2 applicant(s). Admit card email was simulated only.');

        Mail::assertNothingQueued();
    }

    public function test_production_mode_queues_mails_for_selected_paid_applicants(): void
    {
        Mail::fake();
        config()->set('admit_card_mail.dry_run', false);

        $admin = $this->makeAdmin();
        $exam = Exam::factory()->create(['status' => 'active']);

        $paid1 = Application::factory()->create([
            'exam_id' => $exam->id,
            'status' => 'paid',
            'applicant_email' => 'paid1@example.test',
        ]);
        $paid2 = Application::factory()->create([
            'exam_id' => $exam->id,
            'status' => 'paid',
            'applicant_email' => 'paid2@example.test',
        ]);

        $response = $this
            ->actingAs($admin)
            ->from(route('admin.exams.show', $exam))
            ->post(route('admin.exams.send-admit-cards', $exam), [
                'send_scope' => 'selected',
                'active_tab' => 'paid',
                'application_ids' => [$paid1->ulid, $paid2->ulid],
            ]);

        $response->assertRedirect(route('admin.exams.show', $exam));
        $response->assertSessionHas('status', 'Admit card email queued for 2 applicant(s). Emails will be delivered shortly.');

        Mail::assertQueued(AdmitCardMail::class, 2);
    }

    public function test_viva_tab_send_all_targets_only_viva_eligible_paid_applicants(): void
    {
        Mail::fake();
        config()->set('admit_card_mail.dry_run', false);

        $admin = $this->makeAdmin();
        $exam = Exam::factory()->create(['status' => 'active']);

        $vivaSelected = Application::factory()->create([
            'exam_id' => $exam->id,
            'status' => 'paid',
            'selection_stage' => Application::STAGE_VIVA_SELECTED,
            'applicant_email' => 'viva@example.test',
        ]);

        $programSelected = Application::factory()->create([
            'exam_id' => $exam->id,
            'status' => 'paid',
            'selection_stage' => Application::STAGE_PROGRAM_SELECTED,
            'applicant_email' => 'program@example.test',
        ]);

        Application::factory()->create([
            'exam_id' => $exam->id,
            'status' => 'paid',
            'selection_stage' => Application::STAGE_PAID,
            'applicant_email' => 'paid-only@example.test',
        ]);

        $response = $this
            ->actingAs($admin)
            ->from(route('admin.exams.show', $exam))
            ->post(route('admin.exams.send-admit-cards', $exam), [
                'send_scope' => 'all_paid',
                'active_tab' => 'viva',
            ]);

        $response->assertRedirect(route('admin.exams.show', $exam));
        $response->assertSessionHas('status', 'Viva eligibility email queued for 2 applicant(s). Emails will be delivered shortly.');

        Mail::assertQueued(AdmitCardMail::class, function (AdmitCardMail $mail) use ($vivaSelected): bool {
            return $mail->application->is($vivaSelected) && $mail->mailType === 'viva_eligibility';
        });

        Mail::assertQueued(AdmitCardMail::class, function (AdmitCardMail $mail) use ($programSelected): bool {
            return $mail->application->is($programSelected) && $mail->mailType === 'viva_eligibility';
        });

        Mail::assertQueued(AdmitCardMail::class, 2);
    }

    public function test_program_tab_send_selected_queues_program_selection_mail_with_selected_program(): void
    {
        Mail::fake();
        config()->set('admit_card_mail.dry_run', false);

        $admin = $this->makeAdmin();
        $exam = Exam::factory()->create(['status' => 'active']);

        $selected = Application::factory()->create([
            'exam_id' => $exam->id,
            'status' => 'paid',
            'selection_stage' => Application::STAGE_PROGRAM_SELECTED,
            'selected_category_id' => null,
            'applicant_email' => 'program-selected@example.test',
        ]);

        $response = $this
            ->actingAs($admin)
            ->from(route('admin.exams.show', $exam))
            ->post(route('admin.exams.send-admit-cards', $exam), [
                'send_scope' => 'selected',
                'active_tab' => 'program',
                'application_ids' => [$selected->ulid],
            ]);

        $response->assertRedirect(route('admin.exams.show', $exam));
        $response->assertSessionHas('status', 'Program selection email queued for 1 applicant(s). Emails will be delivered shortly.');

        Mail::assertQueued(AdmitCardMail::class, function (AdmitCardMail $mail) use ($selected): bool {
            return $mail->application->is($selected) && $mail->mailType === 'program_selection';
        });
    }

    private function makeAdmin(): User
    {
        $admin = User::factory()->create();
        Role::findOrCreate('admin', 'web');
        $admin->assignRole('admin');

        return $admin;
    }
}

