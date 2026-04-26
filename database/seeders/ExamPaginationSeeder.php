<?php

namespace Database\Seeders;

use App\Models\Exam;
use Carbon\CarbonImmutable;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class ExamPaginationSeeder extends Seeder
{
    private const BATCH_SIZE_PER_STATUS = 18;

    public function run(): void
    {
        $statusMap = [
            'draft' => 'draft',
            'active' => 'active',
            'completed' => 'closed',
        ];

        foreach ($statusMap as $uiStatus => $storedStatus) {
            for ($i = 1; $i <= self::BATCH_SIZE_PER_STATUS; $i++) {
                [$startDate, $endDate] = $this->resolveDateWindow($uiStatus, $i);

                $name = sprintf('Exam %s Batch %02d', ucfirst($uiStatus), $i);

                $exam = Exam::query()->firstOrNew([
                    'name' => $name,
                ]);

                if (blank($exam->ulid)) {
                    $exam->ulid = (string) Str::ulid();
                }

                $exam->description = sprintf('Seeded exam for %s UI pagination testing.', $uiStatus);
                $exam->status = $storedStatus;
                $exam->start_date = $startDate;
                $exam->end_date = $endDate;
                $exam->additional_info = [
                    'seeded_for' => 'ui-pagination',
                    'ui_status' => $uiStatus,
                    'batch' => $i,
                ];

                $exam->save();
            }
        }
    }

    /**
     * @return array{0: CarbonImmutable, 1: CarbonImmutable}
     */
    private function resolveDateWindow(string $uiStatus, int $index): array
    {
        $base = CarbonImmutable::now();

        return match ($uiStatus) {
            'active' => [
                $base->subDays(5 + $index),
                $base->addDays(15 + $index),
            ],
            'completed' => [
                $base->subMonths(3)->subDays($index * 2),
                $base->subMonths(2)->subDays($index),
            ],
            default => [
                $base->addDays(5 + $index),
                $base->addDays(30 + $index),
            ],
        };
    }
}


