<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Application;
use Illuminate\Http\RedirectResponse;

class ApplicationDeleteController extends Controller
{
    public function __invoke(Application $application): RedirectResponse
    {
        // Role enforcement is handled by the 'role:admin' middleware on this route.
        $application->delete();

        return back()->with('status', 'Application deleted successfully.');
    }
}
