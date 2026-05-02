<?php

namespace Database\Seeders;

use App\Models\Application;
use App\Models\Category;
use App\Models\Exam;
use Database\Factories\ApplicationFactory;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class ExamApplicantsSeeder extends Seeder
{
    private const MIN_APPLICANTS_PER_EXAM = 30;

    private const ACTIVE_EXAM_SKIP_MODULO = 3;

    public function run(): void
    {
        $programCategoryIds = Category::query()
            ->where('type', 'program')
            ->pluck('id')
            ->all();

        $targetExams = Exam::query()
            ->whereIn('status', ['active', 'closed'])
            ->orderBy('id')
            ->get();

        $activeExamIndex = 0;
        $closedExamIndex = 0;

        foreach ($targetExams as $exam) {
            $isClosed = $exam->status === 'closed';
            if ($exam->status === 'active') {
                $activeExamIndex++;

                // Keep a subset of active exams empty to reflect real dashboard scenarios.
                if (($activeExamIndex - 1) % self::ACTIVE_EXAM_SKIP_MODULO === 0) {
                    continue;
                }

                $applicantCount = $this->resolveApplicantCount($activeExamIndex);
            } else {
                $closedExamIndex++;
                $applicantCount = $this->resolveApplicantCount($closedExamIndex + 5);
            }

            // Track sequential paid count per exam for application_id generation
            $paidSequence = 0;
            $examYear = $exam->start_date?->year ?? now()->year;

            for ($row = 1; $row <= $applicantCount; $row++) {
                $status         = $this->resolveApplicationStatus($row);
                $gender         = fake()->randomElement(['Male', 'Female', 'Other']);
                $selectionStage = $this->resolveSelectionStage($status, $row, $isClosed);

                $writtenExamMarks = $status === 'paid'
                    ? fake()->randomFloat(2, 35, 100)
                    : null;

                $vivaExamMarks = in_array($selectionStage, [
                    Application::STAGE_VIVA_SELECTED,
                    Application::STAGE_PROGRAM_SELECTED,
                    Application::STAGE_ALUMNI,
                ], true)
                    ? fake()->randomFloat(2, 20, 100)
                    : null;

                $selectedCategoryId = in_array($selectionStage, [
                    Application::STAGE_PROGRAM_SELECTED,
                    Application::STAGE_ALUMNI,
                ], true) && $programCategoryIds !== []
                    ? fake()->randomElement($programCategoryIds)
                    : null;

                // Assign application_id for paid records: YYYYXXXX (e.g. 20260001)
                $applicationId = null;
                if ($status === 'paid') {
                    $paidSequence++;
                    $applicationId = (string) $examYear . sprintf('%04d', $paidSequence);
                }

                $attributes = [
                    'exam_id'         => $exam->id,
                    'applicant_email' => sprintf('exam-%d-applicant-%03d@example.test', $exam->id, $row),
                ];

                $values = [
                    'applicant_name'    => fake()->name(),
                    'applicant_phone'   => sprintf('+8801%09d', ($exam->id * 1000 + $row) % 1000000000),
                    'applicant_nid'     => str_pad((string) ($exam->id * 100000 + $row), 11, '0', STR_PAD_LEFT),
                    'gender'            => $gender,
                    'status'            => $status,
                    'selection_stage'   => $selectionStage,
                    'application_id'    => $applicationId,
                    'transaction_id'    => $status === 'paid'
                        ? sprintf('SEED-TXN-%d-%03d', $exam->id, $row)
                        : null,
                    'payment_amount'    => in_array($status, ['pending', 'paid', 'failed', 'cancelled'], true)
                        ? fake()->randomFloat(2, 500, 5000)
                        : null,
                    'payment_method'    => $status === 'paid' ? 'SSLCommerz' : null,
                    'payment_response'  => $status === 'paid'
                        ? ['provider' => 'sslcommerz', 'seeded' => true]
                        : null,
                    'written_exam_marks'  => $writtenExamMarks,
                    'viva_exam_marks'     => $vivaExamMarks,
                    'selected_category_id'=> $selectedCategoryId,
                    'additional_info'     => ApplicationFactory::fakeAdditionalInfo($gender),
                ];

                $application = Application::query()->firstOrNew($attributes);

                if (blank($application->ulid)) {
                    $application->ulid = (string) Str::ulid();
                }

                $application->fill($values);
                $application->save();
            }
        }
    }

    private function resolveApplicantCount(int $index): int
    {
        return self::MIN_APPLICANTS_PER_EXAM + ($index % 11);
    }

    private function resolveApplicationStatus(int $row): string
    {
        $statuses = ['pending', 'paid', 'submitted', 'approved', 'rejected', 'failed', 'cancelled'];

        return $statuses[$row % count($statuses)];
    }

    private function resolveSelectionStage(string $status, int $row, bool $isClosed = false): ?string
    {
        if ($status !== 'paid') {
            return null;
        }

        return match ($row % 3) {
            0 => $isClosed && ($row % 6 === 0) ? Application::STAGE_ALUMNI : Application::STAGE_PROGRAM_SELECTED,
            1 => Application::STAGE_VIVA_SELECTED,
            default => Application::STAGE_PAID,
        };
    }
}

