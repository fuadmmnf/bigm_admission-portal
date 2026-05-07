<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Application;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Response;

class ApplicationAdmitCardController extends Controller
{
    public function __invoke(Application $application): Response
    {
        $application->loadMissing(['exam', 'selectedCategory']);

        $pdf = Pdf::loadView('pdf.admit-card', [
            'application' => $application,
            'mailType' => 'admit_card',
        ])->setPaper('a4', 'portrait');

        return $pdf->stream('admit-card-'.$application->ulid.'.pdf');
    }
}

