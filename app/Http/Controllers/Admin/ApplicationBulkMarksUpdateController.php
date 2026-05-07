<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Application;
use App\Models\Exam;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;

class ApplicationBulkMarksUpdateController extends Controller
{
    public function updateAssessment(Request $request, Exam $exam): RedirectResponse|JsonResponse
    {
        $validated = $request->validate([
            'active_tab' => ['required', Rule::in(['paid', 'viva', 'program'])],
            'written_marks' => ['nullable', 'array'],
            'written_marks.*' => ['nullable', 'numeric', 'min:0'],
            'viva_marks' => ['nullable', 'array'],
            'viva_marks.*' => ['nullable', 'numeric', 'min:0'],
            'selected_category_ids' => ['nullable', 'array'],
            'selected_category_ids.*' => [
                'nullable',
                'integer',
                Rule::exists('categories', 'id')->where(fn ($query) => $query->where('type', 'program')),
            ],
        ]);

        $activeTab = (string) $validated['active_tab'];

        if ($activeTab === 'paid') {
            $marksByUlid = collect((array) ($validated['written_marks'] ?? []))
                ->map(fn ($value) => $value === '' ? null : $value);

            $eligible = Application::query()
                ->where('exam_id', $exam->id)
                ->where('status', 'paid')
                ->whereIn('ulid', $marksByUlid->keys()->all())
                ->get(['id', 'ulid']);

            DB::transaction(function () use ($eligible, $marksByUlid): void {
                foreach ($eligible as $application) {
                    $application->update([
                        'written_exam_marks' => $marksByUlid->get($application->ulid),
                    ]);
                }
            });

            $message = 'Written marks updated for '.$eligible->count().' paid applicant(s).';

            return $request->expectsJson()
                ? response()->json(['message' => $message])
                : back()->with('status', $message);
        }

        if ($activeTab === 'viva') {
            $marksByUlid = collect((array) ($validated['viva_marks'] ?? []))
                ->map(fn ($value) => $value === '' ? null : $value);

            $eligible = Application::query()
                ->where('exam_id', $exam->id)
                ->where('status', 'paid')
                ->whereIn('selection_stage', [Application::STAGE_VIVA_SELECTED, Application::STAGE_PROGRAM_SELECTED])
                ->whereIn('ulid', $marksByUlid->keys()->all())
                ->get(['id', 'ulid']);

            DB::transaction(function () use ($eligible, $marksByUlid): void {
                foreach ($eligible as $application) {
                    $application->update([
                        'viva_exam_marks' => $marksByUlid->get($application->ulid),
                    ]);
                }
            });

            $message = 'Viva marks updated for '.$eligible->count().' viva-eligible applicant(s).';

            return $request->expectsJson()
                ? response()->json(['message' => $message])
                : back()->with('status', $message);
        }

        // program tab — update selected category
        $categoryByUlid = collect((array) ($validated['selected_category_ids'] ?? []))
            ->map(fn ($value) => $value === '' ? null : $value);

        $eligible = Application::query()
            ->where('exam_id', $exam->id)
            ->where('status', 'paid')
            ->where('selection_stage', Application::STAGE_PROGRAM_SELECTED)
            ->whereIn('ulid', $categoryByUlid->keys()->all())
            ->get(['id', 'ulid']);

        DB::transaction(function () use ($eligible, $categoryByUlid): void {
            foreach ($eligible as $application) {
                $application->update([
                    'selected_category_id' => $categoryByUlid->get($application->ulid),
                ]);
            }
        });

        $message = 'Program selections updated for '.$eligible->count().' enrolled applicant(s).';

        return $request->expectsJson()
            ? response()->json(['message' => $message])
            : back()->with('status', $message);
    }
}
