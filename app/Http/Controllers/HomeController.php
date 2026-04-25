<?php

namespace App\Http\Controllers;

use App\Models\Exam;
use Illuminate\Contracts\View\View;

class HomeController extends Controller
{
    public function __invoke(): View
    {
        $exams = Exam::query()
            ->availableForApplication()
            ->with('category')
            ->orderBy('start_date')
            ->paginate(9);

        return view('pages.home', [
            'exams' => $exams,
        ]);
    }
}

