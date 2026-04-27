<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Mail\AdmitCardMail;
use App\Models\Application;
use App\Models\Exam;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;

class SendAdmitCardController extends Controller
{
    public function __invoke(Request $request, Exam $exam): RedirectResponse
    {
        $request->validate([
            'application_ids'   => ['required', 'array', 'min:1'],
            'application_ids.*' => ['required', 'string'],
        ]);

        $selectedUlids = $request->input('application_ids', []);

        // Fetch only paid applications for this exam that match the selected ULIDs
        $applications = Application::query()
            ->where('exam_id', $exam->id)
            ->where('status', 'paid')
            ->whereIn('ulid', $selectedUlids)
            ->get();

        if ($applications->isEmpty()) {
            return back()->with('error', 'No matching paid applicants found for the selected entries.');
        }

        $sent = 0;
        foreach ($applications as $application) {
            if (! $application->applicant_email) {
                continue;
            }

            Mail::to($application->applicant_email)
                ->queue(new AdmitCardMail($application));

            $sent++;
        }

        return back()->with(
            'status',
            "Admit card dispatch queued for {$sent} applicant(s). Emails will be delivered shortly."
        );
    }
}

