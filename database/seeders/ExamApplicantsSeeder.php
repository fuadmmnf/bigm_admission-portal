<?php

namespace Database\Seeders;

use App\Models\Application;
use App\Models\Category;
use App\Models\Exam;
use Database\Factories\ApplicationFactory;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class ExamApplicantsSeeder extends Seeder
{
    private const MIN_APPLICANTS_PER_EXAM = 30;

    private const ACTIVE_EXAM_SKIP_MODULO = 3;

    /** @var array<string,int> Round-robin index per gender key for face pool */
    private array $facePoolIndex = ['men' => 0, 'women' => 0];

    private const FACE_POOL_SIZE = 10; // portraits/men/1-10 and portraits/women/1-10

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

        $totalActiveExams = (int) $targetExams->where('status', 'active')->count();

        $activeExamIndex = 0;
        $closedExamIndex = 0;

        foreach ($targetExams as $exam) {
            $isClosed = $exam->status === 'closed';
            if ($exam->status === 'active') {
                $activeExamIndex++;

                // Keep a subset of active exams empty only when there are multiple active exams.
                if ($totalActiveExams > 1 && (($activeExamIndex - 1) % self::ACTIVE_EXAM_SKIP_MODULO === 0)) {
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

                $photoPath = $this->seedPhotoFile(
                    'seeded_uploads/photos',
                    sprintf('exam-%d-applicant-%03d-photo.png', $exam->id, $row),
                    $gender,
                );
                $signaturePath = $this->seedSignatureFile(
                    'seeded_uploads/signatures',
                    sprintf('exam-%d-applicant-%03d-signature.png', $exam->id, $row),
                );

                $additionalInfo = ApplicationFactory::fakeAdditionalInfo($gender);
                $additionalInfo['uploads'] = [
                    'applicant_photo' => $photoPath,
                    'signature' => $signaturePath,
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
                    'additional_info'     => $additionalInfo,
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

    private function seedPhotoFile(string $directory, string $filename, string $gender): string
    {
        $relativePath = trim($directory, '/') . '/' . $filename;

        if (Storage::disk('public')->exists($relativePath)) {
            return $relativePath;
        }

        $genderKey = ($gender === 'Female') ? 'women' : 'men';
        $poolIdx   = ($this->facePoolIndex[$genderKey] % self::FACE_POOL_SIZE) + 1;
        $this->facePoolIndex[$genderKey]++;

        $poolPath = "seeded_uploads/_face_pool/{$genderKey}-{$poolIdx}.png";

        if (! Storage::disk('public')->exists($poolPath)) {
            $this->downloadAndResizeFace($genderKey, $poolIdx, $poolPath);
        }

        $data = Storage::disk('public')->get($poolPath);
        Storage::disk('public')->put($relativePath, $data);

        return $relativePath;
    }

    private function downloadAndResizeFace(string $genderKey, int $index, string $poolPath): void
    {
        try {
            $url      = "https://randomuser.me/api/portraits/{$genderKey}/{$index}.jpg";
            $response = Http::timeout(15)->get($url);

            if ($response->successful()) {
                $resized = $this->resizeImageGd($response->body(), 300, 300);
                if ($resized !== null) {
                    Storage::disk('public')->put($poolPath, $resized);
                    $this->command?->getOutput()?->writeln(
                        "  <info>Downloaded face:</info> {$url}"
                    );
                    return;
                }
            }
        } catch (\Throwable $e) {
            Log::warning('Face download failed, using placeholder', [
                'pool_path' => $poolPath,
                'error'     => $e->getMessage(),
            ]);
        }

        // Fallback: generate a simple silhouette placeholder
        Storage::disk('public')->put($poolPath, $this->generatePlaceholderFacePng(300, 300, $genderKey));
    }

    /**
     * Resize raw image bytes to $w×$h PNG using GD (center-crop square).
     */
    private function resizeImageGd(string $data, int $w, int $h): ?string
    {
        if (! extension_loaded('gd')) {
            return null;
        }

        $src = @imagecreatefromstring($data);
        if (! $src) {
            return null;
        }

        $sw = imagesx($src);
        $sh = imagesy($src);

        // Center-crop to square
        $side = min($sw, $sh);
        $srcX = (int)(($sw - $side) / 2);
        $srcY = (int)(($sh - $side) / 2);

        $dst = imagecreatetruecolor($w, $h);
        imagecopyresampled($dst, $src, 0, 0, $srcX, $srcY, $w, $h, $side, $side);
        imagedestroy($src);

        ob_start();
        imagepng($dst);
        $png = ob_get_clean();
        imagedestroy($dst);

        return ($png !== '' && $png !== false) ? $png : null;
    }

    /**
     * Fallback silhouette PNG when download fails or GD is absent.
     */
    private function generatePlaceholderFacePng(int $w, int $h, string $genderKey): string
    {
        if (! extension_loaded('gd')) {
            return base64_decode('iVBORw0KGgoAAAANSUhEUgAAAAEAAAABCAQAAAC1HAwCAAAAC0lEQVR42mP8z8BQDwADhQGAWjR9awAAAABJRU5ErkJggg==');
        }

        $img = imagecreatetruecolor($w, $h);

        // Background
        $bg = imagecolorallocate($img, 210, 215, 220);
        imagefill($img, 0, 0, $bg);

        // Skin tone (varies slightly by gender key for visual distinction)
        $skinR  = $genderKey === 'women' ? 220 : 195;
        $skin   = imagecolorallocate($img, $skinR, 170, 130);
        $shirt  = imagecolorallocate($img, $genderKey === 'women' ? 180 : 80, $genderKey === 'women' ? 100 : 100, $genderKey === 'women' ? 160 : 180);

        // Head (ellipse centered at ~40% down)
        $headCX = (int)($w / 2);
        $headCY = (int)($h * 0.37);
        $headW  = (int)($w * 0.44);
        $headH  = (int)($h * 0.48);
        imagefilledellipse($img, $headCX, $headCY, $headW, $headH, $skin);

        // Body / shoulders (trapezoid approximated with a filled polygon)
        $shoulderY = (int)($h * 0.62);
        $bottomY   = $h;
        $points = [
            (int)($w * 0.18), $bottomY,
            (int)($w * 0.82), $bottomY,
            (int)($w * 0.72), $shoulderY,
            (int)($w * 0.28), $shoulderY,
        ];
        imagefilledpolygon($img, $points, $shirt);

        ob_start();
        imagepng($img);
        $png = ob_get_clean();
        imagedestroy($img);

        return ($png !== '' && $png !== false)
            ? $png
            : base64_decode('iVBORw0KGgoAAAANSUhEUgAAAAEAAAABCAQAAAC1HAwCAAAAC0lEQVR42mP8z8BQDwADhQGAWjR9awAAAABJRU5ErkJggg==');
    }

    private function seedSignatureFile(string $directory, string $filename): string
    {
        $relativePath = trim($directory, '/') . '/' . $filename;

        if (Storage::disk('public')->exists($relativePath)) {
            return $relativePath;
        }

        Storage::disk('public')->put($relativePath, $this->generateSignaturePng(300, 80));

        return $relativePath;
    }

    /**
     * Generate a signature-like PNG (wavy strokes on white background).
     * Uses a hash of $filename as the PRNG seed so each applicant gets a
     * visually distinct but stable signature across repeated seeds.
     */
    private function generateSignaturePng(int $w, int $h): string
    {
        if (! extension_loaded('gd')) {
            return base64_decode('iVBORw0KGgoAAAANSUhEUgAAAAEAAAABCAQAAAC1HAwCAAAAC0lEQVR42mP8z8BQDwADhQGAWjR9awAAAABJRU5ErkJggg==');
        }

        $img   = imagecreatetruecolor($w, $h);
        $white = imagecolorallocate($img, 255, 255, 255);
        $ink   = imagecolorallocate($img, 15, 25, 90);

        imagefill($img, 0, 0, $white);

        // Build a flowing polyline from left to right
        $points = [];
        $x      = mt_rand(8, 18);
        $y      = (int)($h / 2) + mt_rand(-6, 6);
        $points[] = [$x, $y];

        while ($x < $w - 12) {
            $x  = min($x + mt_rand(10, 22), $w - 10);
            $y  = max(6, min($h - 6, $y + mt_rand(-(int)($h * 0.4), (int)($h * 0.4))));
            $points[] = [$x, $y];
        }

        // Draw with 2-px thickness (shift by ±1 on y)
        for ($t = -1; $t <= 1; $t++) {
            for ($i = 1, $n = count($points); $i < $n; $i++) {
                imageline(
                    $img,
                    $points[$i - 1][0], $points[$i - 1][1] + $t,
                    $points[$i][0],     $points[$i][1]     + $t,
                    $ink
                );
            }
        }

        // Add a small decorative loop/arc near the end
        $arcX = (int)($w * 0.75);
        $arcY = (int)($h / 2);
        imagearc($img, $arcX, $arcY, 22, 18, 0, 300, $ink);

        ob_start();
        imagepng($img);
        $png = ob_get_clean();
        imagedestroy($img);

        return ($png !== '' && $png !== false)
            ? $png
            : base64_decode('iVBORw0KGgoAAAANSUhEUgAAAAEAAAABCAQAAAC1HAwCAAAAC0lEQVR42mP8z8BQDwADhQGAWjR9awAAAABJRU5ErkJggg==');
    }
}

