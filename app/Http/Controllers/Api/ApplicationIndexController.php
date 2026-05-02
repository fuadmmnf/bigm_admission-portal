<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\ApplicationResource;
use App\Models\Application;
use Illuminate\Database\Eloquent\Builder;
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
                AllowedFilter::callback('search', function (Builder $query, string $value): void {
                    // Mirror the admin UI: search across name, email, phone, and NID.
                    $query->where(function (Builder $q) use ($value): void {
                        $q->where('applicant_name',  'like', '%'.$value.'%')
                          ->orWhere('applicant_email', 'like', '%'.$value.'%')
                          ->orWhere('applicant_phone', 'like', '%'.$value.'%')
                          ->orWhere('applicant_nid',   'like', '%'.$value.'%');
                    });
                }),
                AllowedFilter::exact('exam_id'),
            )
            ->allowedSorts('applicant_name', 'status', 'created_at')
            ->defaultSort('-created_at')
            ->paginate((int) $request->integer('per_page', 25))
            ->appends($request->query());

        return ApplicationResource::collection($applications);
    }
}

