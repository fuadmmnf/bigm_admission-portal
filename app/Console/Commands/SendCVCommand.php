<?php

namespace App\Console\Commands;

use App\Mail\SendCVMail;
use App\Models\Application;
use App\Models\Exam;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Storage;

class SendCVCommand extends Command
{
    protected $signature = 'cv:send
                            {exam_ulid : Exam ULID}
                            {--skip=0 : Skip the first N matching applicants}';

    protected $description = 'Send CV emails to all paid applicants of an exam';

    public function handle(): int
    {
        $exam = Exam::where('ulid', $this->argument('exam_ulid'))->first();

        if (! $exam) {
            $this->error('Exam not found.');
            return self::FAILURE;
        }

        $applications = Application::query()
            ->where('exam_id', $exam->id)
            ->where('status', 'paid')
            ->orderBy('id')
            ->with(['exam', 'selectedCategory:id,name'])
            ->get();

        if ($applications->isEmpty()) {
            $this->warn('No paid applicants found.');
            return self::SUCCESS;
        }

        $skip = max(0, (int) $this->option('skip'));

        $totalMatching = $applications->count();

        if ($skip >= $totalMatching) {
            $this->warn(
                "Skip value ({$skip}) is greater than or equal to the total matching applicants ({$totalMatching}). Nothing to send."
            );

            return self::SUCCESS;
        }

        if ($skip > 0) {
            $applications = $applications->slice($skip)->values();

            $this->warn("Skipping first {$skip} applicant(s).");
        }

        $this->info("Exam           : {$exam->name}");
        $this->info("Total Matching : {$totalMatching}");
        $this->info("Skipping       : {$skip}");
        $this->info("Processing     : {$applications->count()}");
        $this->newLine();

        $bar = $this->output->createProgressBar($applications->count());
        $bar->start();

        $sent = 0;
        $failed = 0;

        foreach ($applications as $application) {

            if (blank($application->applicant_email)) {
                $bar->advance();
                continue;
            }

            $uploads = data_get($application->additional_info, 'uploads', []);

            $application->setAttribute(
                'photo_data_uri',
                $this->fileToDataUri(data_get($uploads, 'applicant_photo'))
            );

            $application->setAttribute(
                'signature_data_uri',
                $this->fileToDataUri(data_get($uploads, 'signature'))
            );

            try {

                Mail::to($application->applicant_email)
                    ->send(new SendCVMail($application, $exam->name));

                $sent++;

            } catch (\Throwable $e) {

                $failed++;

                Log::error('Failed to send CV email', [
                    'exam_ulid'        => $exam->ulid,
                    'application_ulid' => $application->ulid,
                    'application_id'   => $application->application_id,
                    'recipient'        => $application->applicant_email,
                    'error'            => $e->getMessage(),
                ]);

                $this->newLine();
                $this->error(
                    "Failed: {$application->application_id} ({$application->applicant_email})"
                );
            }

            $bar->advance();
        }

        $bar->finish();

        $this->newLine(2);
        $this->info('Completed.');
        $this->info("Sent   : {$sent}");
        $this->info("Failed : {$failed}");

        return self::SUCCESS;
    }

    private function fileToDataUri(?string $path): ?string
    {
        $normalized = $this->normalizePublicPath($path);

        if ($normalized === null || ! Storage::disk('public')->exists($normalized)) {
            return null;
        }

        $contents = Storage::disk('public')->get($normalized);

        $extension = strtolower(pathinfo($normalized, PATHINFO_EXTENSION));

        $mime = match ($extension) {
            'jpg', 'jpeg' => 'image/jpeg',
            'png'         => 'image/png',
            'webp'        => 'image/webp',
            default       => 'application/octet-stream',
        };

        return 'data:' . $mime . ';base64,' . base64_encode($contents);
    }

    private function normalizePublicPath(?string $path): ?string
    {
        if (blank($path)) {
            return null;
        }

        $normalized = ltrim((string) $path, '/');

        $publicStoragePrefix = trim((string) config('filesystems.disks.public.url', ''), '/') . '/';

        if ($publicStoragePrefix !== '' && str_starts_with($normalized, $publicStoragePrefix)) {
            $normalized = substr($normalized, strlen($publicStoragePrefix));
        }

        if (str_starts_with($normalized, 'public/')) {
            $normalized = substr($normalized, 7);
        }

        if (str_starts_with($normalized, 'storage/')) {
            $normalized = substr($normalized, 8);
        }

        return $normalized;
    }
}
