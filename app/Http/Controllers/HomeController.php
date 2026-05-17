<?php

namespace App\Http\Controllers;

use App\Models\Exam;
use Illuminate\Contracts\View\View;

class HomeController extends Controller
{
    public function __invoke(): View
    {
        $now = now();

        $exams = Exam::query()
            ->where('status', 'active')
            ->where(function ($query) use ($now): void {
                $query->whereNull('end_date')
                    ->orWhere('end_date', '>=', $now);
            })
            // Keep currently open exams first, upcoming exams next.
            ->orderByRaw('CASE WHEN start_date IS NULL OR start_date <= ? THEN 0 ELSE 1 END', [$now])
            ->orderBy('start_date')
            ->paginate(9);

        return view('pages.home', [
            'exams' => $exams,
        ]);
    }
}

