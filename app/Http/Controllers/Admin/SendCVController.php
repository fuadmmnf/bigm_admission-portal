<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Mail\SendCVMail;
use App\Models\Application;
use App\Models\Exam;
use App\Support\ApplicationMedia;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;

class SendCVController extends Controller
{
    public function __invoke(Request $request, Exam $exam): RedirectResponse
    {
        $request->validate([
            'send_scope' => ['required', 'in:selected,all_paid'],
            'active_tab' => ['nullable', 'in:paid,viva,program,alumni'],
            'application_ids' => ['nullable', 'array'],
            'application_ids.*' => ['required_with:application_ids', 'string'],
        ]);

        Log::debug('SendCV request', $request->all());

        $scope = (string)$request->input('send_scope', 'selected');
        $activeTab = (string)$request->input('active_tab', 'paid');
        $selectedUlids = array_values(array_filter((array)$request->input('application_ids', [])));

        if ($scope === 'selected' && count($selectedUlids) === 0) {
            return back()->with('error', 'Select at least one applicant to send CV email notifications.');
        }

        $applicationQuery = Application::query()
            ->where('exam_id', $exam->id)
            ->where('status', 'paid');

        if ($activeTab === 'viva') {
            $applicationQuery->whereIn('selection_stage', [
                Application::STAGE_VIVA_SELECTED,
                Application::STAGE_PROGRAM_SELECTED,
            ]);
        }

        if ($activeTab === 'program') {
            $applicationQuery->where('selection_stage', Application::STAGE_PROGRAM_SELECTED);
        }

        if ($scope === 'selected') {
            $applicationQuery->whereIn('ulid', $selectedUlids);
        }

        $applications = $applicationQuery->with(['exam', 'selectedCategory:id,name'])->get();

        if ($applications->isEmpty()) {
            return back()->with('error', 'No matching applicants found for this tab and action.');
        }

        $dryRun = filter_var(
            config('admit_card_mail.dry_run', true),
            FILTER_VALIDATE_BOOL,
            FILTER_NULL_ON_FAILURE
        );
        $dryRun = $dryRun ?? true;

        $sent = 0;
        foreach ($applications as $application) {
            if (!$application->applicant_email) {
                continue;
            }

            $application = ApplicationMedia::hydrateCvMedia($application);

            if ($dryRun) {
                Log::info('CV dispatch dry-run recipient', [
                    'exam_ulid' => $exam->ulid,
                    'application_ulid' => $application->ulid,
                    'recipient' => $application->applicant_email,
                ]);
            } else {
                try {
                    Mail::to($application->applicant_email)
                        ->send(new SendCVMail($application, $exam->name));

                } catch (\Throwable $e) {
                    Mail::to($application->applicant_email)
                        ->queue(new SendCVMail($application, $exam->name));
                }

            }

            $sent++;
        }

        if ($dryRun) {
            return back()->with(
                'status',
                "DRY RUN complete for {$sent} applicant(s). CV email was simulated only."
            );
        }

        return back()->with(
            'status',
            "CV email queued for {$sent} applicant(s). Emails will be delivered shortly."
        );
    }
}



