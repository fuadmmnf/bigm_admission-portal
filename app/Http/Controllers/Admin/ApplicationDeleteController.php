<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Application;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Auth;

class ApplicationDeleteController extends Controller
{
    public function __invoke(Application $application): RedirectResponse
    {
        abort_unless(Auth::user()?->hasRole('admin'), 403);

        $application->delete();

        return back()->with('status', 'Application deleted successfully.');
    }
}

