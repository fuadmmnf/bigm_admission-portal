<?php

namespace Database\Seeders;

use App\Models\Application;
use App\Models\Exam;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class ExamApplicantsSeeder extends Seeder
{
    private const MIN_APPLICANTS_PER_EXAM = 30;

    private const ACTIVE_EXAM_SKIP_MODULO = 3;

    public function run(): void
    {
        $targetExams = Exam::query()
            ->whereIn('status', ['active', 'closed'])
            ->orderBy('id')
            ->get();

        $activeExamIndex = 0;
        $closedExamIndex = 0;

        foreach ($targetExams as $exam) {
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

            for ($row = 1; $row <= $applicantCount; $row++) {
                $status = $this->resolveApplicationStatus($row);

                $attributes = [
                    'exam_id' => $exam->id,
                    'applicant_email' => sprintf('exam-%d-applicant-%03d@example.test', $exam->id, $row),
                ];

                $values = [
                    'applicant_name' => fake()->name(),
                    'applicant_phone' => sprintf('+8801%09d', ($exam->id * 1000 + $row) % 1000000000),
                    'applicant_id_number' => str_pad((string) ($exam->id * 100000 + $row), 11, '0', STR_PAD_LEFT),
                    'status' => $status,
                    'transaction_id' => $status === 'paid' ? sprintf('SEED-TXN-%d-%03d', $exam->id, $row) : null,
                    'payment_amount' => in_array($status, ['pending', 'paid', 'failed', 'cancelled'], true)
                        ? fake()->randomFloat(2, 500, 5000)
                        : null,
                    'payment_method' => $status === 'paid' ? 'SSLCommerz' : null,
                    'payment_response' => $status === 'paid'
                        ? ['provider' => 'sslcommerz', 'seeded' => true]
                        : null,
                    'additional_info' => [
                        'seeded_for' => 'exam-applicants',
                        'exam_status' => $exam->status,
                        'row' => $row,
                    ],
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
}

