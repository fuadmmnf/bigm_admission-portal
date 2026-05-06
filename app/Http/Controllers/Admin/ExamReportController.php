<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Application;
use App\Models\Category;
use App\Models\Exam;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Support\Collection;
use Illuminate\Contracts\View\View;
use Illuminate\Http\Request;
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
            'genders' => config('applicant_form.genders', []),
            'jobCategories' => config('applicant_form.job_categories', []),
            'programs' => config('applicant_form.programs', []),
            'programCategories' => Category::query()
                ->where('type', 'program')
                ->orderBy('name')
                ->get(['id', 'name', 'additional_info']),
        ]);
    }

    public function attendanceList(Exam $exam): Response
    {
        $applications = $this->paidApplicantsBaseQuery($exam)
            ->get([
                'ulid',
                'application_id',
                'applicant_name',
                'applicant_phone',
                'applicant_email',
                'additional_info',
            ]);
        $applications = $this->attachPhotoDataUris($applications);

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
                'application_id',
                'applicant_name',
                'applicant_phone',
                'applicant_email',
                'written_exam_marks',
                'viva_exam_marks',
                'selection_stage',
                'additional_info',
            ]);
        $applications = $this->attachPhotoDataUris($applications);

        $pdf = Pdf::loadView('reports.viva-selected-list', [
            'exam' => $exam,
            'applications' => $applications,
            'generatedAt' => now(),
        ])->setPaper('a4', 'portrait');

        return $pdf->stream('viva-selected-list-'.$exam->ulid.'.pdf');
    }

    public function genderWiseApplicants(Exam $exam, Request $request): Response
    {
        $gender = $request->query('gender');

        $query = $this->paidApplicantsBaseQuery($exam)
            ->select(['ulid', 'application_id', 'applicant_name', 'gender', 'additional_info']);

        if ($gender) {
            $query->where('gender', $gender);
        }

        $applications = $this->attachPhotoDataUris($query->get());

        $pdf = Pdf::loadView('reports.gender-wise-applicants', [
            'exam' => $exam,
            'applications' => $applications,
            'genderFilter' => $gender,
            'generatedAt' => now(),
        ])->setPaper('a4', 'portrait');

        return $pdf->stream('gender-wise-applicants-'.$exam->ulid.'.pdf');
    }

    public function employerWiseApplicants(Exam $exam, Request $request): Response
    {
        $employer = $request->query('employer');

        $query = $this->paidApplicantsBaseQuery($exam)
            ->select(['ulid', 'application_id', 'applicant_name', 'additional_info']);

        if ($employer) {
            $query->where('additional_info->job_experience->current->job_category', $employer);
        }

        $applications = $this->attachPhotoDataUris($query->get());

        $pdf = Pdf::loadView('reports.employer-wise-applicants', [
            'exam' => $exam,
            'applications' => $applications,
            'employerFilter' => $employer,
            'generatedAt' => now(),
        ])->setPaper('a4', 'portrait');

        return $pdf->stream('employer-wise-applicants-'.$exam->ulid.'.pdf');
    }

    public function choiceListWiseApplicants(Exam $exam): Response
    {
        $applications = $this->paidApplicantsBaseQuery($exam)
            ->select(['ulid', 'application_id', 'applicant_name', 'written_exam_marks', 'viva_exam_marks', 'additional_info'])
            ->get();
        $applications = $this->attachPhotoDataUris($applications);

        $pdf = Pdf::loadView('reports.choice-list-wise-applicants', [
            'exam' => $exam,
            'applications' => $applications,
            'generatedAt' => now(),
        ])->setPaper('a4', 'landscape');

        return $pdf->stream('choice-list-wise-applicants-'.$exam->ulid.'.pdf');
    }

    public function choiceListBySubject(Exam $exam, Request $request): Response
    {
        $subject = $request->query('subject');
        $programs = config('applicant_form.programs', []);

        abort_if(! $subject || ! in_array($subject, $programs), 422, 'A valid subject must be selected.');

        $choiceFields = [
            'first_choice',
            'second_choice',
            'third_choice',
            'fourth_choice',
            'fifth_choice',
            'sixth_choice',
        ];

        $byChoice = [];
        $totalCount = 0;
        foreach ($choiceFields as $field) {
            $group = $this->paidApplicantsBaseQuery($exam)
                ->select(['ulid', 'application_id', 'applicant_name', 'written_exam_marks', 'viva_exam_marks', 'additional_info'])
                ->where('additional_info->course_preferences->'.$field, $subject)
                ->get();

            $byChoice[$field] = $this->attachPhotoDataUris($group);
            $totalCount += $group->count();
        }

        $pdf = Pdf::loadView('reports.choice-list-by-subject', [
            'exam' => $exam,
            'subject' => $subject,
            'byChoice' => $byChoice,
            'totalCount' => $totalCount,
            'generatedAt' => now(),
        ])->setPaper('a4', 'portrait');

        return $pdf->stream('choice-list-by-subject-'.$subject.'-'.$exam->ulid.'.pdf');
    }

    public function jobExperienceWiseApplicants(Exam $exam): Response
    {
        $applications = $this->paidApplicantsBaseQuery($exam)
            ->get(['ulid', 'application_id', 'applicant_name', 'additional_info']);
        $applications = $this->attachPhotoDataUris($applications);

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
            ->with('selectedCategory:id,name')
            ->get(['ulid', 'application_id', 'applicant_name', 'applicant_phone', 'applicant_email', 'selected_category_id', 'additional_info']);
        $applications = $this->attachPhotoDataUris($applications);

        $pdf = Pdf::loadView('reports.enrolled-students', [
            'exam' => $exam,
            'applications' => $applications,
            'generatedAt' => now(),
        ])->setPaper('a4', 'portrait');

        return $pdf->stream('enrolled-students-'.$exam->ulid.'.pdf');
    }

    public function programSelectedByCode(Exam $exam, Request $request): Response
    {
        $programId = (int) $request->query('program_id');

        abort_if($programId <= 0, 422, 'A valid program code must be selected.');

        $programCategory = Category::query()
            ->where('type', 'program')
            ->findOrFail($programId, ['id', 'name', 'additional_info']);

        $applications = $exam->applications()
            ->where('status', 'paid')
            ->whereIn('selection_stage', [Application::STAGE_PROGRAM_SELECTED, Application::STAGE_ALUMNI])
            ->where('selected_category_id', $programCategory->id)
            ->orderBy('application_id')
            ->get([
                'ulid',
                'application_id',
                'applicant_name',
                'applicant_phone',
                'applicant_email',
                'written_exam_marks',
                'viva_exam_marks',
                'selection_stage',
                'additional_info',
            ]);
        $applications = $this->attachPhotoDataUris($applications);

        $pdf = Pdf::loadView('reports.program-selected-by-code', [
            'exam' => $exam,
            'programCategory' => $programCategory,
            'applications' => $applications,
            'generatedAt' => now(),
        ])->setPaper('a4', 'portrait');

        $programCode = data_get($programCategory->additional_info, 'code', $programCategory->name);

        return $pdf->stream('program-selected-'.$programCode.'-'.$exam->ulid.'.pdf');
    }

    /**
     * Generate a batch of applicant CVs as a single PDF.
     *
     * Loading every applicant's photo and signature as base64 all at once is a
     * memory bomb for large exams.  Callers must paginate using ?from_id= and
     * ?limit= (capped at 100) so each request covers at most 100 CVs at a time.
     *
     * Example:
     *   /reports/all-applicant-cvs                          → first 50
     *   /reports/all-applicant-cvs?from_id=20260051         → next batch starting at that ID
     *   /reports/all-applicant-cvs?limit=25&from_id=20260076 → 25 CVs from ID onwards
     */
    public function allApplicantCvs(Exam $exam, Request $request): Response
    {
        $limit  = min(max(1, (int) $request->query('limit', 50)), 100);
        $fromId = $request->query('from_id');

        $query = $exam->applications()
            ->with('selectedCategory:id,name')
            ->orderBy('application_id')
            ->limit($limit);

        if ($fromId !== null && $fromId !== '') {
            $query->where('application_id', '>=', (string) $fromId);
        }

        $applications = $query->get([
            'ulid',
            'application_id',
            'applicant_name',
            'applicant_email',
            'applicant_phone',
            'applicant_nid',
            'gender',
            'status',
            'written_exam_marks',
            'viva_exam_marks',
            'selected_category_id',
            'selection_stage',
            'additional_info',
        ])->map(function (Application $application) {
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

        $suffix = ($fromId !== null && $fromId !== '') ? '-from-'.$fromId : '';

        return $pdf->stream('all-applicant-cvs-'.$exam->ulid.$suffix.'.pdf');
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

    private function attachPhotoDataUris(Collection $applications): Collection
    {
        return $applications->map(function (Application $application) {
            $uploads = data_get($application->additional_info, 'uploads', []);
            $application->setAttribute('photo_data_uri', $this->fileToDataUri(data_get($uploads, 'applicant_photo')));

            return $application;
        });
    }

    private function normalizePublicPath(?string $path): ?string
    {
        if (blank($path)) {
            return null;
        }

        $normalized = ltrim((string) $path, '/');

        $publicStoragePrefix = trim((string) config('filesystems.disks.public.url', ''), '/').'/';
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

