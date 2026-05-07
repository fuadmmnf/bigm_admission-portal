<?php

namespace Database\Seeders;

use App\Models\Exam;
use Carbon\CarbonImmutable;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class ExamPaginationSeeder extends Seeder
{
    /** Only 1 active exam at a time; more drafts/closed for admin pagination testing. */
    private const BATCH_SIZES = [
        'draft' => 18,
        'active' => 1,
        'completed' => 18,
    ];

    public function run(): void
    {
        $statusMap = [
            'draft' => 'draft',
            'active' => 'active',
            'completed' => 'closed',
        ];

        foreach ($statusMap as $uiStatus => $storedStatus) {
            $batchSize = self::BATCH_SIZES[$uiStatus] ?? 18;
            for ($i = 1; $i <= $batchSize; $i++) {
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

                if ($storedStatus === 'active') {
                    $brochurePath = sprintf('exams/brochures/seeded-active-%02d-brochure.pdf', $i);
                    $circularPath = sprintf('exams/circulars/seeded-active-%02d-circular.pdf', $i);

                    $this->seedMockPdf($brochurePath, 'BIGM Admission Brochure');
                    $this->seedMockPdf($circularPath, 'BIGM Admission Circular');

                    $exam->brochure_path = $brochurePath;
                    $exam->circular_path = $circularPath;
                } else {
                    $exam->brochure_path = null;
                    $exam->circular_path = null;
                }

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

    private function seedMockPdf(string $path, string $title): void
    {
        if (Storage::disk('public')->exists($path)) {
            return;
        }

        $safeTitle = str_replace(['(', ')'], '', $title);
        $content = "%PDF-1.4\n1 0 obj\n<< /Type /Catalog /Pages 2 0 R >>\nendobj\n2 0 obj\n<< /Type /Pages /Kids [3 0 R] /Count 1 >>\nendobj\n3 0 obj\n<< /Type /Page /Parent 2 0 R /MediaBox [0 0 595 842] /Contents 4 0 R /Resources << >> >>\nendobj\n4 0 obj\n<< /Length 83 >>\nstream\nBT /F1 16 Tf 60 760 Td (".$safeTitle.") Tj ET\nBT /F1 12 Tf 60 730 Td (Seeded mock document for development.) Tj ET\nendstream\nendobj\nxref\n0 5\n0000000000 65535 f \n0000000010 00000 n \n0000000060 00000 n \n0000000117 00000 n \n0000000232 00000 n \ntrailer\n<< /Size 5 /Root 1 0 R >>\nstartxref\n375\n%%EOF";

        Storage::disk('public')->put($path, $content);
    }
}


