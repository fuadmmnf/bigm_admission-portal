<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Application;
use App\Models\Category;
use Illuminate\Contracts\View\View;

class ApplicationShowController extends Controller
{
    public function __invoke(Application $application): View
    {
        return view('pages.admin-application-show', [
            'application' => $application->load(['exam', 'selectedCategory']),
            'programCategories' => Category::query()
                ->where('type', 'program')
                ->orderBy('name')
                ->get(['id', 'name', 'additional_info']),
        ]);
    }
}

