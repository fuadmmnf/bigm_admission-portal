<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\CategoryResource;
use App\Models\Category;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Spatie\QueryBuilder\AllowedFilter;
use Spatie\QueryBuilder\QueryBuilder;

class CategoryIndexController extends Controller
{
    public function __invoke(Request $request)
    {
        $categories = QueryBuilder::for(Category::query())
            ->with('parent:id,ulid')
            ->allowedFilters(
                AllowedFilter::exact('type'),
                AllowedFilter::partial('search', 'name'),
                AllowedFilter::callback('parent_ulid', function (Builder $query, mixed $value): void {
                    $parentUlid = is_array($value) ? '' : trim((string) $value);

                    if ($parentUlid === '' || in_array($parentUlid, ['null', 'root'], true)) {
                        $query->whereNull('parent_id');

                        return;
                    }

                    $parentId = Category::query()->where('ulid', $parentUlid)->value('id');

                    if (! $parentId) {
                        $query->whereRaw('1 = 0');

                        return;
                    }

                    $query->where('parent_id', $parentId);
                })
            )
            ->allowedSorts('name', 'type', 'created_at')
            ->defaultSort('name')
            ->paginate((int) $request->integer('per_page', 25))
            ->appends($request->query());

        return CategoryResource::collection($categories);
    }
}

