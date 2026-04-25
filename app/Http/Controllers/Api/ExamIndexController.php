<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\ExamResource;
use App\Models\Exam;
use Illuminate\Http\Request;
use Spatie\QueryBuilder\AllowedFilter;
use Spatie\QueryBuilder\QueryBuilder;

class ExamIndexController extends Controller
{
    public function __invoke(Request $request)
    {
        $user = $request->user();
        $baseQuery = Exam::query();

        if (! $user || ! $user->hasAnyRole(['admin', 'moderator'])) {
            $baseQuery->availableForApplication();
        }

        $exams = QueryBuilder::for($baseQuery)
            ->with('category')
            ->allowedFilters(
                AllowedFilter::exact('status'),
                AllowedFilter::partial('search', 'name'),
            )
            ->allowedSorts('name', 'status', 'created_at')
            ->defaultSort('-created_at')
            ->paginate((int) $request->integer('per_page', 25))
            ->appends($request->query());

        return ExamResource::collection($exams);
    }
}
