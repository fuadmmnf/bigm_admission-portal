<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Application;
use App\Models\Category;
use App\Models\Exam;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class ExamPageController extends Controller
{
    public function index(Request $request, string $status): View
    {
        $statusMap = [
            'draft' => 'draft',
            'active' => 'active',
            'complete' => 'closed',
        ];

        abort_unless(isset($statusMap[$status]), 404);

        $resolvedStatus = $statusMap[$status];

        $exams = Exam::query()
            ->where('status', $resolvedStatus)
            ->withCount([
                'applications as paid_applications_count' => fn ($query) => $query->where('status', 'paid'),
            ])
            ->when($request->filled('search'), fn ($query) => $query->where('name', 'like', '%'.$request->string('search').'%'))
            ->orderByDesc('created_at')
            ->paginate(10)
            ->appends($request->query());

        return view('pages.admin-exams-index', [
            'exams' => $exams,
            'currentStatus' => $status,
        ]);
    }

    public function show(Request $request, Exam $exam): View
    {
        $activeTab = $request->string('tab')->toString();
        if (! in_array($activeTab, ['paid', 'viva', 'program'], true)) {
            $activeTab = 'paid';
        }
        $search = trim($request->string('search')->toString());

        $sort = $request->string('sort')->toString();
        $allowedSorts = ['latest', 'written_desc', 'written_asc', 'viva_desc', 'viva_asc'];
        if (! in_array($sort, $allowedSorts, true)) {
            $sort = 'latest';
        }

        $applicationsQuery = $exam->applications()
            ->where('status', 'paid');

        if ($activeTab === 'viva') {
            $applicationsQuery->whereIn('selection_stage', [
                Application::STAGE_VIVA_SELECTED,
                Application::STAGE_PROGRAM_SELECTED,
            ]);
        }

        if ($activeTab === 'program') {
            $applicationsQuery->where('selection_stage', Application::STAGE_PROGRAM_SELECTED);
        }

        if ($search !== '') {
            $applicationsQuery->where(function ($query) use ($search): void {
                $query->where('applicant_name', 'like', '%'.$search.'%')
                    ->orWhere('applicant_email', 'like', '%'.$search.'%')
                    ->orWhere('applicant_phone', 'like', '%'.$search.'%');
            });
        }

        match ($sort) {
            'written_desc' => $applicationsQuery
                ->orderByRaw('written_exam_marks IS NULL')
                ->orderByDesc('written_exam_marks')
                ->orderByDesc('created_at'),
            'written_asc' => $applicationsQuery
                ->orderByRaw('written_exam_marks IS NULL')
                ->orderBy('written_exam_marks')
                ->orderByDesc('created_at'),
            'viva_desc' => $applicationsQuery
                ->orderByRaw('viva_exam_marks IS NULL')
                ->orderByDesc('viva_exam_marks')
                ->orderByDesc('created_at'),
            'viva_asc' => $applicationsQuery
                ->orderByRaw('viva_exam_marks IS NULL')
                ->orderBy('viva_exam_marks')
                ->orderByDesc('created_at'),
            default => $applicationsQuery->latest(),
        };

        $applications = $applicationsQuery
            ->with('selectedCategory:id,name')
            ->paginate(25)
            ->appends($request->query());

        $programCategories = Category::query()
            ->where('type', 'program')
            ->orderBy('name')
            ->get(['id', 'name']);

        $totalPaid = $exam->applications()->where('status', 'paid')->count();
        $totalViva = $exam->applications()
            ->where('status', 'paid')
            ->whereIn('selection_stage', [Application::STAGE_VIVA_SELECTED, Application::STAGE_PROGRAM_SELECTED])
            ->count();
        $totalProgram = $exam->applications()
            ->where('status', 'paid')
            ->where('selection_stage', Application::STAGE_PROGRAM_SELECTED)
            ->count();

        return view('pages.admin-exam-show', [
            'exam'         => $exam,
            'applications' => $applications,
            'totalPaid'    => $totalPaid,
            'totalViva'    => $totalViva,
            'totalProgram' => $totalProgram,
            'activeTab'    => $activeTab,
            'activeSort'   => $sort,
            'activeSearch' => $search,
            'programCategories' => $programCategories,
        ]);
    }

    public function create(): View
    {
        return view('pages.admin-exam-form', [
            'exam' => new Exam(),
            'isEdit' => false,
        ]);
    }

    public function edit(Exam $exam): View
    {
        return view('pages.admin-exam-form', [
            'exam' => $exam,
            'isEdit' => true,
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $this->validateExam($request);

        $exam = Exam::query()->create($validated);

        return redirect()
            ->route('admin.exams.show', $exam)
            ->with('status', 'Exam created successfully.');
    }

    public function update(Request $request, Exam $exam): RedirectResponse
    {
        $validated = $this->validateExam($request);

        $exam->update($validated);

        return redirect()
            ->route('admin.exams.show', $exam)
            ->with('status', 'Exam updated successfully.');
    }

    public function destroy(Exam $exam): RedirectResponse
    {
        $statusRouteMap = [
            'draft' => 'admin.exams.draft',
            'active' => 'admin.exams.active',
            'closed' => 'admin.exams.complete',
        ];

        $redirectRoute = $statusRouteMap[$exam->status] ?? 'admin.exams.active';

        $exam->delete();

        return redirect()
            ->route($redirectRoute)
            ->with('status', 'Exam deleted successfully.');
    }

    private function validateExam(Request $request): array
    {
        return $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
            'status' => ['required', Rule::in(['draft', 'active', 'closed'])],
            'start_date' => ['nullable', 'date'],
            'end_date' => ['nullable', 'date', 'after_or_equal:start_date'],
            'additional_info' => ['nullable', 'array'],
        ]);
    }
}

