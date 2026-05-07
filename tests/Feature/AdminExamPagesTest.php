<?php

namespace Tests\Feature;

use App\Models\Application;
use App\Models\Category;
use App\Models\Exam;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class AdminExamPagesTest extends TestCase
{
    use RefreshDatabase;

    public function test_home_page_shows_only_active_open_exams(): void
    {
        Exam::factory()->create([
            'name' => 'Public Active Exam',
            'status' => 'active',
            'start_date' => now()->subDay(),
            'end_date' => now()->addDay(),
        ]);

        Exam::factory()->create([
            'name' => 'Future Active Exam',
            'status' => 'active',
            'start_date' => now()->addDay(),
            'end_date' => now()->addDays(2),
        ]);

        Exam::factory()->create([
            'name' => 'Draft Hidden Exam',
            'status' => 'draft',
            'start_date' => now()->subDay(),
            'end_date' => now()->addDay(),
        ]);

        $response = $this->get(route('home'));

        $response->assertOk();
        $response->assertSee('Public Active Exam');
        $response->assertDontSee('Future Active Exam');
        $response->assertDontSee('Draft Hidden Exam');
    }

    public function test_admin_can_view_exams_by_status_pages(): void
    {
        $admin = User::factory()->create();
        Role::findOrCreate('admin', 'web');
        $admin->assignRole('admin');

        $this->actingAs($admin);

        Exam::factory()->create(['name' => 'Draft One', 'status' => 'draft']);
        Exam::factory()->create(['name' => 'Active One', 'status' => 'active']);
        Exam::factory()->create(['name' => 'Closed One', 'status' => 'closed']);

        $this->get(route('admin.exams.draft'))
            ->assertOk()
            ->assertSee('Draft One')
            ->assertDontSee('Active One');

        $this->get(route('admin.exams.active'))
            ->assertOk()
            ->assertSee('Active One')
            ->assertDontSee('Closed One');

        $this->get(route('admin.exams.complete'))
            ->assertOk()
            ->assertSee('Closed One')
            ->assertDontSee('Draft One');
    }

    public function test_exam_details_page_shows_only_paid_applicants(): void
    {
        $admin = User::factory()->create();
        Role::findOrCreate('admin', 'web');
        $admin->assignRole('admin');
        $this->actingAs($admin);

        $exam = Exam::factory()->create(['status' => 'active']);

        Application::factory()->create([
            'exam_id' => $exam->id,
            'applicant_name' => 'Paid Applicant',
            'status' => 'paid',
        ]);

        Application::factory()->create([
            'exam_id' => $exam->id,
            'applicant_name' => 'Unpaid Applicant',
            'status' => 'pending',
        ]);

        $response = $this->get(route('admin.exams.show', $exam));

        $response->assertOk();
        $response->assertSee('Paid Applicant');
        $response->assertDontSee('Unpaid Applicant');
        $response->assertSee('Paid Applicants');
        $response->assertSee('Admit Card PDF');
        $response->assertDontSee('Send Email');
    }

    public function test_admin_can_open_single_applicant_details_page(): void
    {
        $admin = User::factory()->create();
        Role::findOrCreate('admin', 'web');
        $admin->assignRole('admin');
        $this->actingAs($admin);

        $exam = Exam::factory()->create(['status' => 'active']);
        $application = Application::factory()->create([
            'exam_id' => $exam->id,
            'status' => 'paid',
            'selection_stage' => Application::STAGE_PAID,
            'applicant_name' => 'Details Candidate',
        ]);

        $response = $this->get(route('admin.applications.show', $application));

        $response->assertOk();
        $response->assertSee('Applicant Details');
        $response->assertSee('Details Candidate');
    }

    public function test_admin_can_stream_single_applicant_admit_card_pdf(): void
    {
        $admin = User::factory()->create();
        Role::findOrCreate('admin', 'web');
        $admin->assignRole('admin');
        $this->actingAs($admin);

        $exam = Exam::factory()->create(['status' => 'active']);
        $application = Application::factory()->create([
            'exam_id' => $exam->id,
            'status' => 'paid',
            'selection_stage' => Application::STAGE_PAID,
            'applicant_name' => 'PDF Candidate',
        ]);

        $response = $this->get(route('admin.applications.admit-card', $application));

        $response->assertOk();
        $response->assertHeader('content-type', 'application/pdf');
    }

    public function test_exam_details_tabs_show_expected_applicant_sets(): void
    {
        $admin = User::factory()->create();
        Role::findOrCreate('admin', 'web');
        $admin->assignRole('admin');
        $this->actingAs($admin);

        $exam = Exam::factory()->create(['status' => 'active']);

        Application::factory()->create([
            'exam_id' => $exam->id,
            'status' => 'paid',
            'selection_stage' => Application::STAGE_PAID,
            'applicant_name' => 'Candidate Paid',
        ]);
        Application::factory()->create([
            'exam_id' => $exam->id,
            'status' => 'paid',
            'selection_stage' => Application::STAGE_VIVA_SELECTED,
            'applicant_name' => 'Candidate Viva',
        ]);
        Application::factory()->create([
            'exam_id' => $exam->id,
            'status' => 'paid',
            'selection_stage' => Application::STAGE_PROGRAM_SELECTED,
            'applicant_name' => 'Candidate Program',
        ]);

        $this->get(route('admin.exams.show', ['exam' => $exam, 'tab' => 'paid']))
            ->assertOk()
            ->assertSee('Candidate Paid')
            ->assertSee('Candidate Viva')
            ->assertSee('Candidate Program');

        $this->get(route('admin.exams.show', ['exam' => $exam, 'tab' => 'viva']))
            ->assertOk()
            ->assertSee('Candidate Viva')
            ->assertSee('Candidate Program');

        $this->get(route('admin.exams.show', ['exam' => $exam, 'tab' => 'program']))
            ->assertOk()
            ->assertSee('Candidate Program');
    }

    public function test_admin_can_mark_selected_paid_applicants_as_viva_selected(): void
    {
        $admin = User::factory()->create();
        Role::findOrCreate('admin', 'web');
        $admin->assignRole('admin');
        $this->actingAs($admin);

        $exam = Exam::factory()->create(['status' => 'active']);
        $a = Application::factory()->create([
            'exam_id' => $exam->id,
            'status' => 'paid',
            'selection_stage' => Application::STAGE_PAID,
        ]);
        $b = Application::factory()->create([
            'exam_id' => $exam->id,
            'status' => 'paid',
            'selection_stage' => Application::STAGE_PAID,
        ]);

        $response = $this->post(route('admin.exams.applications.stage-update', $exam), [
            'target_stage' => Application::STAGE_VIVA_SELECTED,
            'application_ids' => [$a->ulid, $b->ulid],
        ]);

        $response->assertRedirect();

        $this->assertDatabaseHas('applications', [
            'id' => $a->id,
            'selection_stage' => Application::STAGE_VIVA_SELECTED,
        ]);
        $this->assertDatabaseHas('applications', [
            'id' => $b->id,
            'selection_stage' => Application::STAGE_VIVA_SELECTED,
        ]);
    }

    public function test_admin_can_mark_viva_selected_applicants_as_program_selected(): void
    {
        $admin = User::factory()->create();
        Role::findOrCreate('admin', 'web');
        $admin->assignRole('admin');
        $this->actingAs($admin);

        $exam = Exam::factory()->create(['status' => 'active']);
        $viva = Application::factory()->create([
            'exam_id' => $exam->id,
            'status' => 'paid',
            'selection_stage' => Application::STAGE_VIVA_SELECTED,
        ]);
        $paid = Application::factory()->create([
            'exam_id' => $exam->id,
            'status' => 'paid',
            'selection_stage' => Application::STAGE_PAID,
        ]);

        $response = $this->post(route('admin.exams.applications.stage-update', $exam), [
            'target_stage' => Application::STAGE_PROGRAM_SELECTED,
            'application_ids' => [$viva->ulid, $paid->ulid],
        ]);

        $response->assertRedirect();

        $this->assertDatabaseHas('applications', [
            'id' => $viva->id,
            'selection_stage' => Application::STAGE_PROGRAM_SELECTED,
        ]);
        $this->assertDatabaseHas('applications', [
            'id' => $paid->id,
            'selection_stage' => Application::STAGE_PAID,
        ]);
    }

    public function test_moderator_cannot_delete_application(): void
    {
        $moderator = User::factory()->create();
        Role::findOrCreate('moderator', 'web');
        $moderator->assignRole('moderator');

        $exam = Exam::factory()->create(['status' => 'active']);
        $application = Application::factory()->create([
            'exam_id' => $exam->id,
            'status' => 'paid',
        ]);

        $response = $this
            ->actingAs($moderator)
            ->delete(route('admin.applications.destroy', $application));

        $response->assertForbidden();

        $this->assertDatabaseHas('applications', [
            'id' => $application->id,
            'deleted_at' => null,
        ]);
    }

    public function test_exam_show_page_paginates_applicants_list(): void
    {
        $admin = User::factory()->create();
        Role::findOrCreate('admin', 'web');
        $admin->assignRole('admin');
        $this->actingAs($admin);

        $exam = Exam::factory()->create(['status' => 'active']);

        for ($i = 1; $i <= 26; $i++) {
            Application::factory()->create([
                'exam_id' => $exam->id,
                'status' => 'paid',
                'applicant_name' => 'Applicant '.str_pad((string) $i, 2, '0', STR_PAD_LEFT),
                'applicant_email' => 'applicant'.$i.'@example.test',
                'created_at' => now()->startOfDay()->addSeconds($i),
                'updated_at' => now()->startOfDay()->addSeconds($i),
            ]);
        }

        $pageOneResponse = $this->get(route('admin.exams.show', $exam));
        $pageTwoResponse = $this->get(route('admin.exams.show', $exam).'?page=2');

        $pageOneResponse->assertOk()->assertSee('Applicant 26');
        $pageTwoResponse->assertOk()->assertSee('Applicant 01');
    }

    public function test_active_exam_list_page_shows_expected_exam_actions(): void
    {
        $admin = User::factory()->create();
        Role::findOrCreate('admin', 'web');
        $admin->assignRole('admin');
        $this->actingAs($admin);

        Exam::factory()->create(['name' => 'Actionable Exam', 'status' => 'active']);

        $response = $this->get(route('admin.exams.active'));

        $response->assertOk();
        $response->assertSee('Reports');
        $response->assertSee('Applicants');
        $response->assertSee('Edit');
        $response->assertSee('Delete');
        $response->assertSee('data-delete-trigger', false);
        $response->assertSee('delete-exam-modal');
    }

    public function test_admin_can_bulk_update_written_marks_for_all_paid_applicants(): void
    {
        $admin = User::factory()->create();
        Role::findOrCreate('admin', 'web');
        $admin->assignRole('admin');
        $this->actingAs($admin);

        $exam = Exam::factory()->create(['status' => 'active']);
        $paidA = Application::factory()->create([
            'exam_id' => $exam->id,
            'status' => 'paid',
            'selection_stage' => Application::STAGE_PAID,
            'written_exam_marks' => null,
        ]);
        $paidB = Application::factory()->create([
            'exam_id' => $exam->id,
            'status' => 'paid',
            'selection_stage' => Application::STAGE_VIVA_SELECTED,
            'written_exam_marks' => null,
        ]);
        $unpaid = Application::factory()->create([
            'exam_id' => $exam->id,
            'status' => 'pending',
            'written_exam_marks' => null,
        ]);

        $response = $this->patch(route('admin.exams.applications.marks.written', $exam), [
            'marks' => [
                $paidA->ulid => '88.25',
                $paidB->ulid => '91.00',
                $unpaid->ulid => '77.00',
            ],
        ]);

        $response->assertRedirect();

        $this->assertDatabaseHas('applications', ['id' => $paidA->id, 'written_exam_marks' => '88.25']);
        $this->assertDatabaseHas('applications', ['id' => $paidB->id, 'written_exam_marks' => '91.00']);
        $this->assertDatabaseHas('applications', ['id' => $unpaid->id, 'written_exam_marks' => null]);
    }

    public function test_admin_can_bulk_update_viva_marks_only_for_viva_eligible_applicants(): void
    {
        $admin = User::factory()->create();
        Role::findOrCreate('admin', 'web');
        $admin->assignRole('admin');
        $this->actingAs($admin);

        $exam = Exam::factory()->create(['status' => 'active']);
        $viva = Application::factory()->create([
            'exam_id' => $exam->id,
            'status' => 'paid',
            'selection_stage' => Application::STAGE_VIVA_SELECTED,
            'viva_exam_marks' => null,
        ]);
        $program = Application::factory()->create([
            'exam_id' => $exam->id,
            'status' => 'paid',
            'selection_stage' => Application::STAGE_PROGRAM_SELECTED,
            'viva_exam_marks' => null,
        ]);
        $paidOnly = Application::factory()->create([
            'exam_id' => $exam->id,
            'status' => 'paid',
            'selection_stage' => Application::STAGE_PAID,
            'viva_exam_marks' => null,
        ]);

        $response = $this->patch(route('admin.exams.applications.marks.viva', $exam), [
            'marks' => [
                $viva->ulid => '74.50',
                $program->ulid => '82.00',
                $paidOnly->ulid => '66.50',
            ],
        ]);

        $response->assertRedirect();

        $this->assertDatabaseHas('applications', ['id' => $viva->id, 'viva_exam_marks' => '74.50']);
        $this->assertDatabaseHas('applications', ['id' => $program->id, 'viva_exam_marks' => '82.00']);
        $this->assertDatabaseHas('applications', ['id' => $paidOnly->id, 'viva_exam_marks' => null]);
    }

    public function test_exam_show_page_can_sort_by_written_marks_descending(): void
    {
        $admin = User::factory()->create();
        Role::findOrCreate('admin', 'web');
        $admin->assignRole('admin');
        $this->actingAs($admin);

        $exam = Exam::factory()->create(['status' => 'active']);

        Application::factory()->create([
            'exam_id' => $exam->id,
            'status' => 'paid',
            'selection_stage' => Application::STAGE_PAID,
            'applicant_name' => 'Candidate Alpha',
            'written_exam_marks' => 45.00,
        ]);
        Application::factory()->create([
            'exam_id' => $exam->id,
            'status' => 'paid',
            'selection_stage' => Application::STAGE_PAID,
            'applicant_name' => 'Candidate Bravo',
            'written_exam_marks' => 92.00,
        ]);
        Application::factory()->create([
            'exam_id' => $exam->id,
            'status' => 'paid',
            'selection_stage' => Application::STAGE_PAID,
            'applicant_name' => 'Candidate Charlie',
            'written_exam_marks' => 70.00,
        ]);

        $response = $this->get(route('admin.exams.show', ['exam' => $exam, 'sort' => 'written_desc']));

        $response->assertOk();
        $response->assertSeeInOrder(['Candidate Bravo', 'Candidate Charlie', 'Candidate Alpha']);
    }

    public function test_exam_show_page_can_search_paid_applicants_by_name_email_or_phone(): void
    {
        $admin = User::factory()->create();
        Role::findOrCreate('admin', 'web');
        $admin->assignRole('admin');
        $this->actingAs($admin);

        $exam = Exam::factory()->create(['status' => 'active']);

        Application::factory()->create([
            'exam_id' => $exam->id,
            'status' => 'paid',
            'selection_stage' => Application::STAGE_PAID,
            'applicant_name' => 'Search Name Target',
            'applicant_email' => 'target-name@example.test',
            'applicant_phone' => '01711111111',
        ]);
        Application::factory()->create([
            'exam_id' => $exam->id,
            'status' => 'paid',
            'selection_stage' => Application::STAGE_PAID,
            'applicant_name' => 'Other Person',
            'applicant_email' => 'target-email@example.test',
            'applicant_phone' => '01722222222',
        ]);
        Application::factory()->create([
            'exam_id' => $exam->id,
            'status' => 'paid',
            'selection_stage' => Application::STAGE_PAID,
            'applicant_name' => 'Third Person',
            'applicant_email' => 'third@example.test',
            'applicant_phone' => '01733333333',
        ]);

        $this->get(route('admin.exams.show', ['exam' => $exam, 'tab' => 'paid', 'search' => 'Search Name']))
            ->assertOk()
            ->assertSee('Search Name Target')
            ->assertDontSee('Other Person');

        $this->get(route('admin.exams.show', ['exam' => $exam, 'tab' => 'paid', 'search' => 'target-email@example.test']))
            ->assertOk()
            ->assertSee('Other Person')
            ->assertDontSee('Search Name Target');

        $this->get(route('admin.exams.show', ['exam' => $exam, 'tab' => 'paid', 'search' => '01733333333']))
            ->assertOk()
            ->assertSee('Third Person')
            ->assertDontSee('Search Name Target');
    }

    public function test_exam_show_search_remains_scoped_to_selected_tab(): void
    {
        $admin = User::factory()->create();
        Role::findOrCreate('admin', 'web');
        $admin->assignRole('admin');
        $this->actingAs($admin);

        $exam = Exam::factory()->create(['status' => 'active']);

        Application::factory()->create([
            'exam_id' => $exam->id,
            'status' => 'paid',
            'selection_stage' => Application::STAGE_PAID,
            'applicant_name' => 'Scoped Candidate',
            'applicant_email' => 'scoped@example.test',
        ]);

        $response = $this->get(route('admin.exams.show', ['exam' => $exam, 'tab' => 'viva', 'search' => 'Scoped Candidate']));

        $response->assertOk();
        $response->assertSee('No applicants found for this tab.');
        $response->assertSee('Clear');
    }

    public function test_admin_can_bulk_update_written_marks_via_inline_assessment_action(): void
    {
        $admin = User::factory()->create();
        Role::findOrCreate('admin', 'web');
        $admin->assignRole('admin');
        $this->actingAs($admin);

        $exam = Exam::factory()->create(['status' => 'active']);
        $paid = Application::factory()->create([
            'exam_id' => $exam->id,
            'status' => 'paid',
            'selection_stage' => Application::STAGE_PAID,
            'written_exam_marks' => null,
        ]);

        $response = $this->patch(route('admin.exams.applications.assessment.bulk', $exam), [
            'active_tab' => 'paid',
            'written_marks' => [
                $paid->ulid => '64.75',
            ],
        ]);

        $response->assertRedirect();
        $this->assertDatabaseHas('applications', [
            'id' => $paid->id,
            'written_exam_marks' => '64.75',
        ]);
    }

    public function test_viva_tab_shows_previous_written_marks(): void
    {
        $admin = User::factory()->create();
        Role::findOrCreate('admin', 'web');
        $admin->assignRole('admin');
        $this->actingAs($admin);

        $exam = Exam::factory()->create(['status' => 'active']);
        Application::factory()->create([
            'exam_id' => $exam->id,
            'status' => 'paid',
            'selection_stage' => Application::STAGE_VIVA_SELECTED,
            'applicant_name' => 'Carry Forward Candidate',
            'written_exam_marks' => 71.25,
            'viva_exam_marks' => null,
        ]);

        $response = $this->get(route('admin.exams.show', ['exam' => $exam, 'tab' => 'viva']));

        $response->assertOk();
        $response->assertSee('Carry Forward Candidate');
        $response->assertSee('71.25');
        $response->assertSee('name="viva_marks[', false);
    }

    public function test_admin_can_update_paid_application_assessment_details(): void
    {
        $admin = User::factory()->create();
        Role::findOrCreate('admin', 'web');
        $admin->assignRole('admin');
        $this->actingAs($admin);

        $exam = Exam::factory()->create(['status' => 'active']);
        $program = Category::factory()->create([
            'type' => 'program',
            'name' => 'Master of Public Health',
        ]);
        $application = Application::factory()->create([
            'exam_id' => $exam->id,
            'status' => 'paid',
            'selection_stage' => Application::STAGE_PROGRAM_SELECTED,
        ]);

        $response = $this->patch(route('admin.applications.assessment.update', $application), [
            'written_exam_marks' => '77.50',
            'viva_exam_marks' => '81.25',
            'selected_category_id' => $program->id,
        ]);

        $response->assertRedirect();

        $this->assertDatabaseHas('applications', [
            'id' => $application->id,
            'written_exam_marks' => '77.50',
            'viva_exam_marks' => '81.25',
            'selected_category_id' => $program->id,
        ]);
    }

    public function test_admin_can_view_assessment_details_on_single_applicant_page(): void
    {
        $admin = User::factory()->create();
        Role::findOrCreate('admin', 'web');
        $admin->assignRole('admin');
        $this->actingAs($admin);

        $exam = Exam::factory()->create(['status' => 'active']);
        $program = Category::factory()->create([
            'type' => 'program',
            'name' => 'MBA',
        ]);
        $application = Application::factory()->create([
            'exam_id' => $exam->id,
            'status' => 'paid',
            'selection_stage' => Application::STAGE_PROGRAM_SELECTED,
            'written_exam_marks' => 74.50,
            'viva_exam_marks' => 69.00,
            'selected_category_id' => $program->id,
            'applicant_name' => 'Assessment Candidate',
        ]);

        $response = $this->get(route('admin.applications.show', $application));

        $response->assertOk();
        $response->assertSee('Assessment &amp; Selection', false);
        $response->assertSee('74.50');
        $response->assertSee('69.00');
        $response->assertSee('MBA');
    }

    public function test_admin_can_view_exam_reports_page(): void
    {
        $admin = User::factory()->create();
        Role::findOrCreate('admin', 'web');
        $admin->assignRole('admin');
        $this->actingAs($admin);

        $exam = Exam::factory()->create(['status' => 'active']);

        $response = $this->get(route('admin.exams.reports.index', $exam));

        $response->assertOk();
        $response->assertSee('Exam Reports');
        $response->assertSee('Download Attendance Sheet');
        $response->assertSee('Download Viva Sheet');
        $response->assertSee('Download Gender Report');
        $response->assertSee('Download Employer Report');
        $response->assertSee('Download Choice Report');
        $response->assertSee('Download Enrolled Students');
        $response->assertSee('Download Program CVs');
    }

    public function test_admin_can_stream_attendance_pdf_report(): void
    {
        $admin = User::factory()->create();
        Role::findOrCreate('admin', 'web');
        $admin->assignRole('admin');
        $this->actingAs($admin);

        $exam = Exam::factory()->create(['status' => 'active']);

        Application::factory()->create([
            'exam_id' => $exam->id,
            'status' => 'paid',
            'applicant_name' => 'Paid Candidate',
        ]);

        Application::factory()->create([
            'exam_id' => $exam->id,
            'status' => 'pending',
            'applicant_name' => 'Pending Candidate',
        ]);

        $response = $this->get(route('admin.exams.reports.attendance-list', $exam));

        $response->assertOk();
        $response->assertHeader('content-type', 'application/pdf');
    }

    public function test_moderator_cannot_download_attendance_pdf_report(): void
    {
        $moderator = User::factory()->create();
        Role::findOrCreate('moderator', 'web');
        $moderator->assignRole('moderator');
        $this->actingAs($moderator);

        $exam = Exam::factory()->create(['status' => 'active']);

        $response = $this->get(route('admin.exams.reports.attendance-list', $exam));

        $response->assertForbidden();
    }

    public function test_admin_can_stream_viva_selected_pdf_report(): void
    {
        $admin = User::factory()->create();
        Role::findOrCreate('admin', 'web');
        $admin->assignRole('admin');
        $this->actingAs($admin);

        $exam = Exam::factory()->create(['status' => 'active']);

        Application::factory()->create([
            'exam_id' => $exam->id,
            'status' => 'paid',
            'selection_stage' => Application::STAGE_VIVA_SELECTED,
            'applicant_name' => 'Viva Candidate',
        ]);

        Application::factory()->create([
            'exam_id' => $exam->id,
            'status' => 'paid',
            'selection_stage' => Application::STAGE_PAID,
            'applicant_name' => 'Paid Only Candidate',
        ]);

        $response = $this->get(route('admin.exams.reports.viva-selected-list', $exam));

        $response->assertOk();
        $response->assertHeader('content-type', 'application/pdf');
    }

    public function test_moderator_cannot_download_viva_selected_pdf_report(): void
    {
        $moderator = User::factory()->create();
        Role::findOrCreate('moderator', 'web');
        $moderator->assignRole('moderator');
        $this->actingAs($moderator);

        $exam = Exam::factory()->create(['status' => 'active']);

        $response = $this->get(route('admin.exams.reports.viva-selected-list', $exam));

        $response->assertForbidden();
    }

    public function test_admin_can_stream_all_new_exam_reports(): void
    {
        $admin = User::factory()->create();
        Role::findOrCreate('admin', 'web');
        $admin->assignRole('admin');
        $this->actingAs($admin);

        $exam = Exam::factory()->create(['status' => 'active']);

        Application::factory()->create([
            'exam_id' => $exam->id,
            'status' => 'paid',
            'selection_stage' => Application::STAGE_PROGRAM_SELECTED,
            'applicant_name' => 'Final Candidate',
            'additional_info' => [
                'course_preferences' => [
                    'first_choice' => 'HRM',
                ],
            ],
        ]);

        $programCategory = Category::factory()->create([
            'type' => 'program',
            'name' => 'Human Resource Management',
            'additional_info' => ['code' => 'HRM'],
        ]);

        $routes = [
            'admin.exams.reports.gender-wise-applicants',
            'admin.exams.reports.employer-wise',
            'admin.exams.reports.choice-list-wise',
            'admin.exams.reports.enrolled-students',
        ];

        foreach ($routes as $routeName) {
            $response = $this->get(route($routeName, $exam));
            $response->assertOk();
            $response->assertHeader('content-type', 'application/pdf');
        }

        $response = $this->get(route('admin.exams.reports.all-applicant-cvs', [
            'exam' => $exam,
            'program_id' => $programCategory->id,
        ]));

        $response->assertOk();
        $response->assertHeader('content-type', 'application/pdf');
    }

    public function test_moderator_cannot_download_new_exam_reports(): void
    {
        $moderator = User::factory()->create();
        Role::findOrCreate('moderator', 'web');
        $moderator->assignRole('moderator');
        $this->actingAs($moderator);

        $exam = Exam::factory()->create(['status' => 'active']);

        $routes = [
            'admin.exams.reports.gender-wise-applicants',
            'admin.exams.reports.employer-wise',
            'admin.exams.reports.choice-list-wise',
            'admin.exams.reports.enrolled-students',
        ];

        foreach ($routes as $routeName) {
            $this->get(route($routeName, $exam))->assertForbidden();
        }

        $this->get(route('admin.exams.reports.all-applicant-cvs', [
            'exam' => $exam,
            'program_id' => 1,
        ]))->assertForbidden();
    }

    public function test_program_wise_cv_report_requires_program_id(): void
    {
        $admin = User::factory()->create();
        Role::findOrCreate('admin', 'web');
        $admin->assignRole('admin');
        $this->actingAs($admin);

        $exam = Exam::factory()->create(['status' => 'active']);

        $this->get(route('admin.exams.reports.all-applicant-cvs', $exam))
            ->assertStatus(422);
    }

    public function test_admin_can_delete_exam_from_list_action(): void
    {
        $admin = User::factory()->create();
        Role::findOrCreate('admin', 'web');
        $admin->assignRole('admin');
        $this->actingAs($admin);

        $exam = Exam::factory()->create(['name' => 'Disposable Exam', 'status' => 'active']);

        $response = $this->delete(route('admin.exams.destroy', $exam));

        $response->assertRedirect(route('admin.exams.active'));

        $this->assertSoftDeleted('exams', [
            'id' => $exam->id,
            'name' => 'Disposable Exam',
        ]);
    }

    public function test_create_and_edit_use_same_exam_form_view(): void
    {
        $admin = User::factory()->create();
        Role::findOrCreate('admin', 'web');
        $admin->assignRole('admin');
        $this->actingAs($admin);

        $exam = Exam::factory()->create();

        $this->get(route('admin.exams.create'))
            ->assertOk()
            ->assertViewIs('pages.admin-exam-form');

        $this->get(route('admin.exams.edit', $exam))
            ->assertOk()
            ->assertViewIs('pages.admin-exam-form');
    }

    public function test_admin_can_create_and_update_exam(): void
    {
        $admin = User::factory()->create();
        Role::findOrCreate('admin', 'web');
        $admin->assignRole('admin');
        $this->actingAs($admin);

        $storeResponse = $this->post(route('admin.exams.store'), [
            'name' => 'Combined Cadet Exam',
            'description' => 'National level admission exam',
            'status' => 'draft',
            'start_date' => now()->addDay()->toDateTimeString(),
            'end_date' => now()->addDays(3)->toDateTimeString(),
        ]);

        $created = Exam::query()->where('name', 'Combined Cadet Exam')->firstOrFail();

        $storeResponse->assertRedirect(route('admin.exams.show', $created));

        $this->assertDatabaseHas('exams', [
            'name' => 'Combined Cadet Exam',
            'status' => 'draft',
        ]);

        $updateResponse = $this->put(route('admin.exams.update', $created), [
            'name' => 'Combined Cadet Exam 2026',
            'description' => 'Updated copy',
            'status' => 'active',
            'start_date' => now()->addDay()->toDateTimeString(),
            'end_date' => now()->addDays(5)->toDateTimeString(),
        ]);

        $updateResponse->assertRedirect(route('admin.exams.show', $created));

        $this->assertDatabaseHas('exams', [
            'id' => $created->id,
            'name' => 'Combined Cadet Exam 2026',
            'status' => 'active',
        ]);
    }
}

