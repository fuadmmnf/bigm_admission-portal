<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Mail\AdmitCardMail;
use App\Models\Application;
use App\Models\Exam;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;

class SendAdmitCardController extends Controller
{
    public function __invoke(Request $request, Exam $exam): RedirectResponse
    {
        $request->validate([
            'send_scope' => ['required', 'in:selected,all_paid'],
            'application_ids' => ['nullable', 'array'],
            'application_ids.*' => ['required_with:application_ids', 'string'],
        ]);

        $scope = (string) $request->input('send_scope', 'selected');
        $selectedUlids = array_values(array_filter((array) $request->input('application_ids', [])));

        if ($scope === 'selected' && count($selectedUlids) === 0) {
            return back()->with('error', 'Select at least one paid applicant to send admit cards.');
        }

        $applicationQuery = Application::query()
            ->where('exam_id', $exam->id)
            ->where('status', 'paid');

        if ($scope === 'selected') {
            $applicationQuery->whereIn('ulid', $selectedUlids);
        }

        $applications = $applicationQuery->with('exam')->get();

        if ($applications->isEmpty()) {
            return back()->with('error', 'No matching paid applicants found for this action.');
        }

        $dryRun = filter_var(
            config('admit_card_mail.dry_run', true),
            FILTER_VALIDATE_BOOL,
            FILTER_NULL_ON_FAILURE
        );
        $dryRun = $dryRun ?? true;

        $sent = 0;
        foreach ($applications as $application) {
            if (! $application->applicant_email) {
                continue;
            }

            if ($dryRun) {
                Log::info('Admit card dispatch dry-run recipient', [
                    'exam_ulid' => $exam->ulid,
                    'application_ulid' => $application->ulid,
                    'recipient' => $application->applicant_email,
                ]);
            } else {
                Mail::to($application->applicant_email)
                    ->queue(new AdmitCardMail($application));
            }

            $sent++;
        }

        if ($dryRun) {
            return back()->with(
                'status',
                "DRY RUN complete for {$sent} applicant(s). No real email was sent."
            );
        }

        return back()->with(
            'status',
            "Admit card dispatch queued for {$sent} applicant(s). Emails will be delivered shortly."
        );
    }
}

