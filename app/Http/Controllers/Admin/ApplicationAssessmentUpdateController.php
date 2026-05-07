<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Application;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class ApplicationAssessmentUpdateController extends Controller
{
    public function __invoke(Request $request, Application $application): RedirectResponse
    {
        abort_unless($application->status === 'paid', 404);

        $validated = $request->validate([
            'written_exam_marks' => ['nullable', 'numeric', 'min:0'],
            'viva_exam_marks' => ['nullable', 'numeric', 'min:0'],
            'selected_category_id' => [
                'nullable',
                'integer',
                Rule::exists('categories', 'id')->where(fn ($query) => $query->where('type', 'program')),
            ],
        ]);

        $application->update([
            'written_exam_marks' => $validated['written_exam_marks'] ?? null,
            'viva_exam_marks' => $validated['viva_exam_marks'] ?? null,
            'selected_category_id' => $validated['selected_category_id'] ?? null,
        ]);

        return back()->with('status', 'Assessment details updated successfully.');
    }
}


