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
                            {--tab=paid : paid|viva|program}';

    protected $description = 'Send admit card related emails to applicants';

    public function handle(): int
    {
        $exam = Exam::where('ulid', $this->argument('exam_ulid'))->first();

        if (! $exam) {
            $this->error('Exam not found.');
            return self::FAILURE;
        }

        $tab = strtolower($this->option('tab'))?? 'paid';

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
            ->with(['exam', 'selectedCategory:id,name'])
            ->get();

        if ($applications->isEmpty()) {
            $this->warn('No matching applicants found.');
            return self::SUCCESS;
        }

        $mailType = match ($tab) {
            'viva' => 'viva_eligibility',
            'program' => 'program_selection',
            default => 'admit_card',
        };

        $this->info("Sending {$mailType} emails to {$applications->count()} applicants...");

        $bar = $this->output->createProgressBar($applications->count());
        $bar->start();

        $sent = 0;
        $failed = 0;

        foreach ($applications as $application) {

            if (! $application->applicant_email) {
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
                    'application_ulid' => $application->ulid,
                    'email' => $application->applicant_email,
                    'error' => $e->getMessage(),
                ]);

                $this->newLine();
                $this->error("Failed: {$application->application_id} ({$application->applicant_email})");
            }

            $bar->advance();
        }

        $bar->finish();

        $this->newLine();
        $this->info("Completed.");
        $this->info("Sent   : {$sent}");
        $this->info("Failed : {$failed}");

        return self::SUCCESS;
    }
}
