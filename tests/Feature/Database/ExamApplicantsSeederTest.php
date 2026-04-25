<?php

namespace Tests\Feature\Database;

use App\Models\Exam;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ExamApplicantsSeederTest extends TestCase
{
    use RefreshDatabase;

    public function test_database_seeder_generates_applicants_for_completed_exams_and_some_active_exams(): void
    {
        $this->seed();

        $closedExams = Exam::query()
            ->where('status', 'closed')
            ->withCount('applications')
            ->get();

        $this->assertGreaterThan(0, $closedExams->count());

        foreach ($closedExams as $exam) {
            $this->assertGreaterThanOrEqual(
                30,
                $exam->applications_count,
                'Closed exam did not receive the minimum applicant volume: '.$exam->name
            );
        }

        $activeExams = Exam::query()
            ->where('status', 'active')
            ->withCount('applications')
            ->get();

        $this->assertGreaterThan(0, $activeExams->count());
        $this->assertGreaterThan(0, $activeExams->where('applications_count', 0)->count());
        $this->assertGreaterThan(0, $activeExams->where('applications_count', '>=', 30)->count());
    }

    public function test_database_seeder_does_not_seed_applicants_into_draft_exams(): void
    {
        $this->seed();

        $draftExams = Exam::query()
            ->where('status', 'draft')
            ->withCount('applications')
            ->get();

        $this->assertGreaterThan(0, $draftExams->count());
        $this->assertSame(0, $draftExams->sum('applications_count'));
    }
}

