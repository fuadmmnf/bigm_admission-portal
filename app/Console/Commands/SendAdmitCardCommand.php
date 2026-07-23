<?php

namespace App\Console\Commands;

use App\Mail\AdmitCardMail;
use App\Models\Application;
use App\Models\Exam;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;

class SendAdmitCardCommand extends Command
{
    protected $signature = 'admit:send
                            {exam_ulid : Exam ULID}
                            {--tab=paid : paid|viva|program}
                            {--skip=0 : Skip the first N matching applicants}';

    protected $description = 'Send admit card related emails to applicants';

    public function handle(): int
    {
        $exam = Exam::where('ulid', $this->argument('exam_ulid'))->first();

        if (! $exam) {
            $this->error('Exam not found.');
            return self::FAILURE;
        }

        $tab = strtolower($this->option('tab') ?? 'paid');

        if (! in_array($tab, ['paid', 'viva', 'program'])) {
            $this->error('Invalid tab. Use paid, viva or program.');
            return self::FAILURE;
        }

        $query = Application::query()
            ->where('exam_id', $exam->id)
            ->where('status', 'paid');

        if ($tab === 'viva') {
            $query->whereIn('selection_stage', [
                Application::STAGE_VIVA_SELECTED,
                Application::STAGE_PROGRAM_SELECTED,
            ]);
        }

        if ($tab === 'program') {
            $query->where('selection_stage', Application::STAGE_PROGRAM_SELECTED);
        }

        $applications = $query
            ->orderBy('id') // deterministic ordering
            ->with(['exam', 'selectedCategory:id,name'])
            ->get();

        if ($applications->isEmpty()) {
            $this->warn('No matching applicants found.');
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

        $mailType = match ($tab) {
            'viva' => 'viva_eligibility',
            'program' => 'program_selection',
            default => 'admit_card',
        };

        $this->info("Exam           : {$exam->name}");
        $this->info("Mail Type      : {$mailType}");
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

            try {

                Mail::to($application->applicant_email)
                    ->send(new AdmitCardMail($application, $mailType));

                $sent++;

            } catch (\Throwable $e) {

                $failed++;

                Log::error('Failed to send admit card email', [
                    'exam_ulid' => $exam->ulid,
                    'application_ulid' => $application->ulid,
                    'application_id' => $application->application_id,
                    'recipient' => $application->applicant_email,
                    'mail_type' => $mailType,
                    'error' => $e->getMessage(),
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
}
