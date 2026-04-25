<?php

namespace Database\Seeders;

use App\Models\Category;
use App\Models\Exam;
use Carbon\CarbonImmutable;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class ExamPaginationSeeder extends Seeder
{
    private const BATCH_SIZE_PER_STATUS_PER_PROGRAM = 6;

    public function run(): void
    {
        $programCategories = Category::query()
            ->where('type', 'program')
            ->orderBy('name')
            ->get();

        if ($programCategories->isEmpty()) {
            $this->call(ProgramCategoriesSeeder::class);

            $programCategories = Category::query()
                ->where('type', 'program')
                ->orderBy('name')
                ->get();
        }

        $statusMap = [
            'draft' => 'draft',
            'active' => 'active',
            'completed' => 'closed',
        ];

        foreach ($programCategories as $programCategory) {
            $programCode = data_get($programCategory->additional_info, 'code', 'PROGRAM');

            foreach ($statusMap as $uiStatus => $storedStatus) {
                for ($i = 1; $i <= self::BATCH_SIZE_PER_STATUS_PER_PROGRAM; $i++) {
                    [$startDate, $endDate] = $this->resolveDateWindow($uiStatus, $i);

                    $name = sprintf('%s %s Batch %02d', $programCode, ucfirst($uiStatus), $i);

                    $exam = Exam::query()->firstOrNew([
                        'category_id' => $programCategory->id,
                        'name' => $name,
                    ]);

                    if (blank($exam->ulid)) {
                        $exam->ulid = (string) Str::ulid();
                    }

                    $exam->description = sprintf('%s course exam seeded for %s UI pagination testing.', $programCategory->name, $uiStatus);
                    $exam->status = $storedStatus;
                    $exam->start_date = $startDate;
                    $exam->end_date = $endDate;
                    $exam->additional_info = [
                        'seeded_for' => 'ui-pagination',
                        'ui_status' => $uiStatus,
                        'program_code' => $programCode,
                        'batch' => $i,
                    ];

                    $exam->save();
                }
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


