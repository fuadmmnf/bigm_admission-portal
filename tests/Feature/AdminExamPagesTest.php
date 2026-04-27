<?php

namespace Tests\Feature;

use App\Models\Application;
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
            'applicant_name' => 'Paid Only',
        ]);
        Application::factory()->create([
            'exam_id' => $exam->id,
            'status' => 'paid',
            'selection_stage' => Application::STAGE_VIVA_SELECTED,
            'applicant_name' => 'Viva Selected',
        ]);
        Application::factory()->create([
            'exam_id' => $exam->id,
            'status' => 'paid',
            'selection_stage' => Application::STAGE_PROGRAM_SELECTED,
            'applicant_name' => 'Program Selected',
        ]);

        $this->get(route('admin.exams.show', ['exam' => $exam, 'tab' => 'paid']))
            ->assertOk()
            ->assertSee('Paid Only')
            ->assertSee('Viva Selected')
            ->assertSee('Program Selected');

        $this->get(route('admin.exams.show', ['exam' => $exam, 'tab' => 'viva']))
            ->assertOk()
            ->assertDontSee('Paid Only')
            ->assertSee('Viva Selected')
            ->assertSee('Program Selected');

        $this->get(route('admin.exams.show', ['exam' => $exam, 'tab' => 'program']))
            ->assertOk()
            ->assertDontSee('Paid Only')
            ->assertDontSee('Viva Selected')
            ->assertSee('Program Selected');
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
        $response->assertSee('aria-label="View applicants"', false);
        $response->assertSee('aria-label="Edit exam"', false);
        $response->assertSee('aria-label="Delete exam"', false);
        $response->assertSee('data-delete-trigger', false);
        $response->assertSee('delete-exam-modal');
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

