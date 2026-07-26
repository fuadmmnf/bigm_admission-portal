<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Application;
use App\Support\ApplicationMedia;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Response;

class ApplicationCvController extends Controller
{
    public function __invoke(Application $application): Response
    {
        $application = ApplicationMedia::hydrateCvMedia($application);

        $pdf = Pdf::loadView('reports.individual-cv', [
            'application' => $application,
        ])->setPaper('a4', 'portrait');

        return $pdf->stream('cv-'.$application->ulid.'.pdf');
    }
}
