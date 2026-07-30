<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Application;
use App\Models\Category;
use App\Models\Exam;
use App\Support\ApplicationMedia;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Support\Collection;
use Illuminate\Contracts\View\View;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use PhpOffice\PhpWord\PhpWord;
use PhpOffice\PhpWord\IOFactory;
use Symfony\Component\HttpFoundation\StreamedResponse;

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

//    public function attendanceList(Exam $exam): Response
//    {
//        ini_set('memory_limit', '1024M');
//        set_time_limit(300);
//        $applications = $this->paidApplicantsBaseQuery($exam)
//            ->reorder()
//            ->orderBy('application_id')
//            ->get([
//                'ulid',
//                'application_id',
//                'applicant_name',
//                'applicant_phone',
//                'applicant_email',
//                'additional_info',
//            ]);
//        $applications = $this->attachPhotoDataUris($applications);
//
//        $pdf = Pdf::loadView('reports.attendance-list', [
//            'exam' => $exam,
//            'applications' => $applications,
//            'generatedAt' => now(),
//        ])->setPaper('a4', 'portrait');
//
//        return $pdf->stream('attendance-list-'.$exam->ulid.'.pdf');
//    }

    public function getPhotoPath($application)
    {
        if (!$application->photo) {
            return null;
        }

        return Storage::disk('public')->path($application->photo);
    }
    public function attendanceList(Exam $exam)
    {
        ini_set('memory_limit', '1024M');
        set_time_limit(300);

        $applications = $this->paidApplicantsBaseQuery($exam)
            ->reorder()
            ->orderBy('application_id')
            ->get([
                'ulid',
                'application_id',
                'applicant_name',
                'additional_info',
            ]);

        $phpWord = new PhpWord();

        $section = $phpWord->addSection([
            'marginTop' => 800,
            'marginBottom' => 800,
            'marginLeft' => 600,
            'marginRight' => 600,
        ]);

        $section->addText(
            $exam->name,
            ['bold' => true, 'size' => 16],
            ['alignment' => 'center']
        );

        $section->addText(
            'Attendance Sheet',
            ['size' => 11],
            ['alignment' => 'center']
        );

        $table = $section->addTable([
            'borderSize' => 6,
            'borderColor' => '000000',
            'cellMargin' => 80,
        ]);

        // Header
        $table->addRow();

        $table->addCell(800)->addText('SL', ['bold' => true]);
        $table->addCell(1800)->addText('Application ID', ['bold' => true]);
        $table->addCell(3500)->addText('Name', ['bold' => true]);
        $table->addCell(1500)->addText('Photo', ['bold' => true]);
        $table->addCell(2500)->addText('Signature', ['bold' => true]);

        foreach ($applications as $i => $application) {

            $table->addRow(900);

            $table->addCell()->addText($i + 1);

            $table->addCell()->addText(
                $application->application_id ?? $application->ulid
            );

            $table->addCell()->addText($application->applicant_name);

            $photoCell = $table->addCell();

            if ($path = $this->getPhotoPath($application)) {

                $photoCell->addImage($path, [
                    'width' => 45,
                    'height' => 45,
                ]);

            } else {

                $photoCell->addText('Photo');

            }

            $table->addCell(); // blank signature cell
        }

        $tempFile = storage_path('app/temp_attendance.docx');

        IOFactory::createWriter($phpWord, 'Word2007')->save($tempFile);

        return response()->download(
            $tempFile,
            'attendance-list.docx'
        )->deleteFileAfterSend(true);
    }
    public function vivaSheet(Exam $exam): Response
    {
        ini_set('memory_limit', '1024M');
        set_time_limit(300);
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

        $pdf = Pdf::loadView('reports.viva-sheet', [
            'exam' => $exam,
            'applications' => $applications,
            'generatedAt' => now(),
            'pageOrientation' => 'landscape',
        ])->setPaper('a4', 'landscape');

        return $pdf->stream('viva-sheet-'.$exam->ulid.'.pdf');
    }

    public function genderWiseApplicants(Exam $exam, Request $request): Response
    {
        ini_set('memory_limit', '1024M');
        set_time_limit(300);
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
        ini_set('memory_limit', '1024M');
        set_time_limit(300);
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
        ini_set('memory_limit', '1024M');
        set_time_limit(300);
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
        ini_set('memory_limit', '1024M');
        set_time_limit(300);
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


    public function enrolledStudents(Exam $exam): Response
    {
        ini_set('memory_limit', '1024M');
        set_time_limit(300);
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
        ini_set('memory_limit', '1024M');
        set_time_limit(300);
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

    public function allApplicantCvs(Exam $exam, Request $request): Response
    {
        ini_set('memory_limit', '1024M');
        set_time_limit(300);
        $programId = (int) $request->query('program_id');

        abort_if($programId <= 0, 422, 'A valid program code must be selected.');

        $programCategory = Category::query()
            ->where('type', 'program')
            ->findOrFail($programId, ['id', 'name', 'additional_info']);

        $selectedProgramCode = $this->normalizeProgramCode(
            data_get($programCategory->additional_info, 'code', $programCategory->name)
        );

        $query = $exam->applications()
            ->where('status', 'paid')
            ->with('selectedCategory:id,name')
            ->orderBy('application_id');

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
        ])->filter(function (Application $application) use ($selectedProgramCode): bool {
            $firstChoiceCode = $this->extractFirstChoiceProgramCode($application);

            return $firstChoiceCode !== null && $firstChoiceCode === $selectedProgramCode;
        })->values()->map(function (Application $application) {
            return ApplicationMedia::hydrateCvMedia($application);
        });

        $pdf = Pdf::loadView('reports.all-applicant-cvs', [
            'exam' => $exam,
            'programCategory' => $programCategory,
            'selectedProgramCode' => $selectedProgramCode,
            'applications' => $applications,
            'generatedAt' => now(),
        ])->setPaper('a4', 'portrait');

        $safeCode = Str::slug($selectedProgramCode, '-');

        return $pdf->stream('program-wise-cvs-'.$safeCode.'-'.$exam->ulid.'.pdf');
    }

    private function extractFirstChoiceProgramCode(Application $application): ?string
    {
        $firstChoice = data_get($application->additional_info, 'course_preferences.first_choice');

        return $this->normalizeProgramCode($firstChoice);
    }

    private function normalizeProgramCode(mixed $value): ?string
    {
        $text = trim((string) $value);
        if ($text === '') {
            return null;
        }

        if (str_contains($text, ' - ')) {
            $text = trim((string) explode(' - ', $text, 2)[0]);
        }

        $firstToken = preg_split('/\s+/', $text)[0] ?? $text;

        return strtoupper(trim((string) $firstToken));
    }

    private function attachPhotoDataUris(Collection $applications): Collection
    {
        return $applications->map(function (Application $application) {
            $uploads = data_get($application->additional_info, 'uploads', []);
            $application->setAttribute('photo_data_uri', ApplicationMedia::fileToDataUri(data_get($uploads, 'applicant_photo')));

            return $application;
        });
    }
}
