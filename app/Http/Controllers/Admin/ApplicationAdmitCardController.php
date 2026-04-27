<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Application;
use Illuminate\Contracts\View\View;

class ApplicationAdmitCardController extends Controller
{
    public function __invoke(Application $application): View
    {
        return view('pages.applicant-admit-card', [
            'application' => $application->load('exam'),
        ]);
    }
}

