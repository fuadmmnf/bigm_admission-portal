<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
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
                'applications as unpaid_applications_count' => fn ($query) => $query->where('status', '!=', 'paid'),
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

    public function show(Exam $exam): View
    {
        $applications = $exam->applications()
            ->latest()
            ->paginate(15);

        return view('pages.admin-exam-show', [
            'exam' => $exam,
            'applications' => $applications,
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

