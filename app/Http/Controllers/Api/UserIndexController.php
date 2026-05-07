<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\UserResource;
use App\Models\User;
use Illuminate\Http\Request;
use Spatie\QueryBuilder\AllowedFilter;
use Spatie\QueryBuilder\QueryBuilder;

class UserIndexController extends Controller
{
    public function __invoke(Request $request)
    {
        $users = QueryBuilder::for(User::query())
            ->allowedFields('ulid', 'name', 'email', 'created_at')
            ->allowedFilters(
                AllowedFilter::partial('name'),
                AllowedFilter::partial('email'),
            )
            ->allowedSorts('name', 'email', 'created_at')
            ->defaultSort('-created_at')
            ->paginate((int) $request->integer('per_page', 15))
            ->appends($request->query());

        return UserResource::collection($users);
    }
}


