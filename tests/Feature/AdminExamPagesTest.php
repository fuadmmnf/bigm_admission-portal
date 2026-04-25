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

    public function test_exam_details_page_shows_paid_and_unpaid_applicants(): void
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
        $response->assertSee('Unpaid Applicant');
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

        $category = Category::factory()->create(['type' => 'exam']);

        $storeResponse = $this->post(route('admin.exams.store'), [
            'name' => 'Combined Cadet Exam',
            'category_id' => $category->id,
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
            'category_id' => $category->id,
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

