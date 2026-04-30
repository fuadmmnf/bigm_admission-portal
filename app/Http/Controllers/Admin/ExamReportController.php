<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Application;
use App\Models\Exam;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Contracts\View\View;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Storage;

class ExamReportController extends Controller
{
    private function paidApplicantsBaseQuery(Exam $exam)
    {
        return $exam->applications()
            ->where('status', 'paid')
            ->orderBy('applicant_name');
    }

    public function index(Exam $exam): View
    {
        $paidApplicantsCount = $exam->applications()->where('status', 'paid')->count();
        $vivaSelectedCount = $exam->applications()
            ->where('status', 'paid')
            ->whereIn('selection_stage', [Application::STAGE_VIVA_SELECTED, Application::STAGE_PROGRAM_SELECTED])
            ->count();
        $programSelectedCount = $exam->applications()
            ->where('status', 'paid')
            ->where('selection_stage', Application::STAGE_PROGRAM_SELECTED)
            ->count();

        return view('pages.admin-exam-reports', [
            'exam' => $exam,
            'paidApplicantsCount' => $paidApplicantsCount,
            'vivaSelectedCount' => $vivaSelectedCount,
            'programSelectedCount' => $programSelectedCount,
        ]);
    }

    public function attendanceList(Exam $exam): Response
    {
        $applications = $this->paidApplicantsBaseQuery($exam)
            ->get([
                'ulid',
                'applicant_name',
                'applicant_phone',
                'applicant_email',
            ]);

        $pdf = Pdf::loadView('reports.attendance-list', [
            'exam' => $exam,
            'applications' => $applications,
            'generatedAt' => now(),
        ])->setPaper('a4', 'portrait');

        return $pdf->stream('attendance-list-'.$exam->ulid.'.pdf');
    }

    public function vivaSelectedList(Exam $exam): Response
    {
        $applications = $this->paidApplicantsBaseQuery($exam)
            ->whereIn('selection_stage', [Application::STAGE_VIVA_SELECTED, Application::STAGE_PROGRAM_SELECTED])
            ->get([
                'ulid',
                'applicant_name',
                'applicant_phone',
                'applicant_email',
                'selection_stage',
            ]);

        $pdf = Pdf::loadView('reports.viva-selected-list', [
            'exam' => $exam,
            'applications' => $applications,
            'generatedAt' => now(),
        ])->setPaper('a4', 'portrait');

        return $pdf->stream('viva-selected-list-'.$exam->ulid.'.pdf');
    }

    public function genderWiseApplicants(Exam $exam): Response
    {
        $applications = $this->paidApplicantsBaseQuery($exam)
            ->get(['ulid', 'applicant_name', 'gender', 'additional_info']);

        $pdf = Pdf::loadView('reports.gender-wise-applicants', [
            'exam' => $exam,
            'applications' => $applications,
            'generatedAt' => now(),
        ])->setPaper('a4', 'portrait');

        return $pdf->stream('gender-wise-applicants-'.$exam->ulid.'.pdf');
    }

    public function employerWiseApplicants(Exam $exam): Response
    {
        $applications = $this->paidApplicantsBaseQuery($exam)
            ->get(['ulid', 'applicant_name', 'additional_info']);

        $pdf = Pdf::loadView('reports.employer-wise-applicants', [
            'exam' => $exam,
            'applications' => $applications,
            'generatedAt' => now(),
        ])->setPaper('a4', 'portrait');

        return $pdf->stream('employer-wise-applicants-'.$exam->ulid.'.pdf');
    }

    public function choiceListWiseApplicants(Exam $exam): Response
    {
        $applications = $this->paidApplicantsBaseQuery($exam)
            ->get(['ulid', 'applicant_name', 'additional_info']);

        $pdf = Pdf::loadView('reports.choice-list-wise-applicants', [
            'exam' => $exam,
            'applications' => $applications,
            'generatedAt' => now(),
        ])->setPaper('a4', 'portrait');

        return $pdf->stream('choice-list-wise-applicants-'.$exam->ulid.'.pdf');
    }

    public function jobExperienceWiseApplicants(Exam $exam): Response
    {
        $applications = $this->paidApplicantsBaseQuery($exam)
            ->get(['ulid', 'applicant_name', 'additional_info']);

        $pdf = Pdf::loadView('reports.job-experience-wise-applicants', [
            'exam' => $exam,
            'applications' => $applications,
            'generatedAt' => now(),
        ])->setPaper('a4', 'portrait');

        return $pdf->stream('job-experience-wise-applicants-'.$exam->ulid.'.pdf');
    }

    public function enrolledStudents(Exam $exam): Response
    {
        $applications = $this->paidApplicantsBaseQuery($exam)
            ->where('selection_stage', Application::STAGE_PROGRAM_SELECTED)
            ->get(['ulid', 'applicant_name', 'applicant_phone', 'applicant_email']);

        $pdf = Pdf::loadView('reports.enrolled-students', [
            'exam' => $exam,
            'applications' => $applications,
            'generatedAt' => now(),
        ])->setPaper('a4', 'portrait');

        return $pdf->stream('enrolled-students-'.$exam->ulid.'.pdf');
    }

    public function allApplicantCvs(Exam $exam): Response
    {
        $applications = $exam->applications()
            ->orderBy('applicant_name')
            ->get([
                'ulid',
                'applicant_name',
                'applicant_email',
                'applicant_phone',
                'applicant_id_number',
                'gender',
                'status',
                'selection_stage',
                'additional_info',
            ])
            ->map(function (Application $application) {
                $uploads = data_get($application->additional_info, 'uploads', []);

                $application->setAttribute('photo_data_uri', $this->fileToDataUri(data_get($uploads, 'applicant_photo')));
                $application->setAttribute('signature_data_uri', $this->fileToDataUri(data_get($uploads, 'signature')));

                return $application;
            });

        $pdf = Pdf::loadView('reports.all-applicant-cvs', [
            'exam' => $exam,
            'applications' => $applications,
            'generatedAt' => now(),
        ])->setPaper('a4', 'portrait');

        return $pdf->stream('all-applicant-cvs-'.$exam->ulid.'.pdf');
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
            'png' => 'image/png',
            'webp' => 'image/webp',
            default => 'application/octet-stream',
        };

        return 'data:'.$mime.';base64,'.base64_encode($contents);
    }

    private function normalizePublicPath(?string $path): ?string
    {
        if (blank($path)) {
            return null;
        }

        $normalized = ltrim((string) $path, '/');

        if (str_starts_with($normalized, 'public/')) {
            $normalized = substr($normalized, 7);
        }

        return $normalized;
    }
}


