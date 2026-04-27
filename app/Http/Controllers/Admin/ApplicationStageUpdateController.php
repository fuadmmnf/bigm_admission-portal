<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Application;
use App\Models\Exam;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class ApplicationStageUpdateController extends Controller
{
    public function __invoke(Request $request, Exam $exam): RedirectResponse
    {
        $validated = $request->validate([
            'target_stage' => ['required', 'in:'.Application::STAGE_VIVA_SELECTED.','.Application::STAGE_PROGRAM_SELECTED],
            'application_ids' => ['required', 'array', 'min:1'],
            'application_ids.*' => ['required', 'string'],
        ]);

        $targetStage = (string) $validated['target_stage'];
        $applicationIds = (array) $validated['application_ids'];

        $query = Application::query()
            ->where('exam_id', $exam->id)
            ->where('status', 'paid')
            ->whereIn('ulid', $applicationIds);

        if ($targetStage === Application::STAGE_PROGRAM_SELECTED) {
            $query->whereIn('selection_stage', [Application::STAGE_VIVA_SELECTED, Application::STAGE_PROGRAM_SELECTED]);
        }

        if ($targetStage === Application::STAGE_VIVA_SELECTED) {
            $query->where(function ($builder): void {
                $builder->whereNull('selection_stage')
                    ->orWhere('selection_stage', Application::STAGE_PAID)
                    ->orWhere('selection_stage', Application::STAGE_VIVA_SELECTED);
            });
        }

        $updated = $query->update(['selection_stage' => $targetStage]);

        if ($updated === 0) {
            return back()->with('error', 'No eligible paid applicants were found for this update.');
        }

        $label = $targetStage === Application::STAGE_VIVA_SELECTED ? 'Viva Selected' : 'Program Selected';

        return back()->with('status', "Updated {$updated} applicant(s) to {$label}.");
    }
}

