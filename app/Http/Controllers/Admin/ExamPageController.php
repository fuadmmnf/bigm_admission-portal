<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
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
            ->with('category')
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
        $exam->load('category');

        $paidApplications = $exam->applications()
            ->where('status', 'paid')
            ->latest()
            ->paginate(10, ['*'], 'paid_page');

        $unpaidApplications = $exam->applications()
            ->where('status', '!=', 'paid')
            ->latest()
            ->paginate(10, ['*'], 'unpaid_page');

        return view('pages.admin-exam-show', [
            'exam' => $exam,
            'paidApplications' => $paidApplications,
            'unpaidApplications' => $unpaidApplications,
        ]);
    }

    public function create(): View
    {
        return view('pages.admin-exam-form', [
            'exam' => new Exam(),
            'categories' => Category::query()->where('type', 'exam')->orderBy('name')->get(),
            'isEdit' => false,
        ]);
    }

    public function edit(Exam $exam): View
    {
        return view('pages.admin-exam-form', [
            'exam' => $exam,
            'categories' => Category::query()->where('type', 'exam')->orderBy('name')->get(),
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

    private function validateExam(Request $request): array
    {
        return $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'category_id' => ['required', 'integer', Rule::exists('categories', 'id')],
            'description' => ['nullable', 'string'],
            'status' => ['required', Rule::in(['draft', 'active', 'closed'])],
            'start_date' => ['nullable', 'date'],
            'end_date' => ['nullable', 'date', 'after_or_equal:start_date'],
            'additional_info' => ['nullable', 'array'],
        ]);
    }
}

