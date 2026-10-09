<?php

namespace App\Http\Controllers\Public;

use App\Http\Controllers\Controller;
use App\Models\Application;
use App\Models\Exam;
use App\Support\ApplicationMedia;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\View\View;

class DocumentDownloadController extends Controller
{
    /**
     * Show the public document download form.
     */
    public function showForm(): View
    {
        $exams = Exam::where('status', 'active')
            ->orderBy('name')
            ->get();

        return view('pages.public-document-download', [
            'exams' => $exams,
        ]);
    }

    /**
     * Search for an application using email and NID.
     */
    public function searchApplication(Request $request)
    {
        $validated = $request->validate([
            'exam_id' => 'required|exists:exams,id',
            'email' => 'required|email',
            'nid' => 'required|string|min:5',
        ]);

        $application = Application::where('exam_id', $validated['exam_id'])
            ->where('applicant_email', $validated['email'])
            ->where('applicant_nid', $validated['nid'])
            ->first();

        if (! $application) {
            return response()->json([
                'found' => false,
                'message' => 'No application found with the provided email and NID.',
            ], 404);
        }

        return response()->json([
            'found' => true,
            'application' => [
                'ulid' => $application->ulid,
                'applicant_name' => $application->applicant_name,
                'application_id' => $application->application_id,
            ],
        ]);
    }

    /**
     * Download CV for an application.
     */
    public function downloadCV(Request $request)
    {
        $validated = $request->validate([
            'exam_id' => 'required|exists:exams,id',
            'email' => 'required|email',
            'nid' => 'required|string|min:5',
        ]);

        $application = Application::where('exam_id', $validated['exam_id'])
            ->where('applicant_email', $validated['email'])
            ->where('applicant_nid', $validated['nid'])
            ->first();

        abort_if(! $application, 404, 'Application not found.');

        $application = ApplicationMedia::hydrateCvMedia($application);

        $pdf = Pdf::loadView('reports.individual-cv', [
            'application' => $application,
        ])->setPaper('a4', 'portrait');

        return $pdf->stream('cv-' . $application->ulid . '.pdf');
    }

    /**
     * Download admit card for an application.
     */
    public function downloadAdmitCard(Request $request): Response
    {
        $validated = $request->validate([
            'exam_id' => 'required|exists:exams,id',
            'email' => 'required|email',
            'nid' => 'required|string|min:5',
        ]);

        $application = Application::where('exam_id', $validated['exam_id'])
            ->where('applicant_email', $validated['email'])
            ->where('applicant_nid', $validated['nid'])
            ->first();

        abort_if(! $application, 404, 'Application not found.');

        $application->loadMissing(['exam', 'selectedCategory']);

        $pdf = Pdf::loadView('pdf.admit-card', [
            'application' => $application,
            'mailType' => 'admit_card',
        ])->setPaper('a4', 'portrait');

        return $pdf->stream('admit-card-' . $application->ulid . '.pdf');
    }
}
