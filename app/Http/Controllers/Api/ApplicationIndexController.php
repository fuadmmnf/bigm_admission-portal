<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\ApplicationResource;
use App\Models\Application;
use Illuminate\Http\Request;
use Spatie\QueryBuilder\AllowedFilter;
use Spatie\QueryBuilder\QueryBuilder;

class ApplicationIndexController extends Controller
{
    public function __invoke(Request $request)
    {
        $applications = QueryBuilder::for(Application::query())
            ->with('exam')
            ->allowedFilters(
                AllowedFilter::exact('status'),
                AllowedFilter::partial('search', 'applicant_name'),
                AllowedFilter::exact('exam_id'),
            )
            ->allowedSorts('applicant_name', 'status', 'created_at')
            ->defaultSort('-created_at')
            ->paginate((int) $request->integer('per_page', 25))
            ->appends($request->query());

        return ApplicationResource::collection($applications);
    }
}

