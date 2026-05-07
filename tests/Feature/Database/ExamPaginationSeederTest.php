<?php

namespace Tests\Feature\Database;

use App\Models\Exam;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ExamPaginationSeederTest extends TestCase
{
    use RefreshDatabase;

    public function test_database_seeder_creates_exam_batches_for_all_ui_statuses(): void
    {
        $this->seed();

        $this->assertSame(18, Exam::query()->where('status', 'draft')->count());
        $this->assertSame(18, Exam::query()->where('status', 'active')->count());
        $this->assertSame(18, Exam::query()->where('status', 'closed')->count());
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

