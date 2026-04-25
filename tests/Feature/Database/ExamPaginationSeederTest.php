<?php

namespace Tests\Feature\Database;

use App\Models\Category;
use App\Models\Exam;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ExamPaginationSeederTest extends TestCase
{
    use RefreshDatabase;

    public function test_database_seeder_creates_exams_for_all_program_categories_and_all_ui_statuses(): void
    {
        $this->seed();

        $programCategories = Category::query()->where('type', 'program')->get();

        $this->assertGreaterThan(0, $programCategories->count());

        foreach ($programCategories as $programCategory) {
            $this->assertSame(
                6,
                Exam::query()->where('category_id', $programCategory->id)->where('status', 'draft')->count(),
                'Draft exam count mismatch for category '.$programCategory->name
            );

            $this->assertSame(
                6,
                Exam::query()->where('category_id', $programCategory->id)->where('status', 'active')->count(),
                'Active exam count mismatch for category '.$programCategory->name
            );

            $this->assertSame(
                6,
                Exam::query()->where('category_id', $programCategory->id)->where('status', 'closed')->count(),
                'Completed/closed exam count mismatch for category '.$programCategory->name
            );
        }
    }

    public function test_seeded_exam_volume_is_large_enough_for_status_pagination(): void
    {
        $this->seed();

        // Admin pages currently paginate exams at 10 per page.
        $this->assertGreaterThanOrEqual(11, Exam::query()->where('status', 'draft')->count());
        $this->assertGreaterThanOrEqual(11, Exam::query()->where('status', 'active')->count());
        $this->assertGreaterThanOrEqual(11, Exam::query()->where('status', 'closed')->count());
    }

    public function test_completed_ui_status_is_persisted_as_closed_with_metadata(): void
    {
        $this->seed();

        $completedUiCount = Exam::query()
            ->where('status', 'closed')
            ->where('additional_info->ui_status', 'completed')
            ->count();

        $this->assertGreaterThanOrEqual(11, $completedUiCount);
    }
}

