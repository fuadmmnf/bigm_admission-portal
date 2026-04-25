<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\CategoryResource;
use App\Models\Category;
use Illuminate\Http\Request;

class CategoryIndexController extends Controller
{
    public function __invoke(Request $request)
    {
        $categories = Category::query()
            ->with('parent:id,ulid')
            ->when($request->filled('type'), function ($query) use ($request): void {
                $query->where('type', $request->string('type')->toString());
            })
            ->when($request->has('parent_ulid'), function ($query) use ($request): void {
                $parentUlid = $request->string('parent_ulid')->toString();

                if (blank($parentUlid) || in_array($parentUlid, ['null', 'root'], true)) {
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
            ->orderBy('name')
            ->paginate((int) $request->integer('per_page', 25))
            ->appends($request->query());

        return CategoryResource::collection($categories);
    }
}

