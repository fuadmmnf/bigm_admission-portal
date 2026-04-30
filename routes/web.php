<?php

use App\Http\Controllers\Admin\ExamPageController;
use App\Http\Controllers\Admin\ApplicationAdmitCardController;
use App\Http\Controllers\Admin\ApplicationDeleteController;
use App\Http\Controllers\Admin\ApplicationShowController;
use App\Http\Controllers\Admin\ApplicationStageUpdateController;
use App\Http\Controllers\Admin\SendAdmitCardController;
use App\Http\Controllers\Applicant\ApplicationFormController;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\Payment\PaymentController;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Route;
use Livewire\Volt\Volt;

Route::get('/', HomeController::class)->name('home');

Route::get('/apply/{exam:ulid}', [ApplicationFormController::class, 'create'])->name('applications.create');
Route::post('/apply/{exam:ulid}', [ApplicationFormController::class, 'store'])->name('applications.store');

Volt::route('/admin/login', 'admin-login')->name('admin-login')->middleware('guest');

Route::middleware([
    'auth:sanctum',
    config('jetstream.auth_session'),
    'verified',
    'role:admin|moderator',
])->group(function (): void {
    Volt::route('/admin/dashboard', 'admin-dashboard')->name('admin-dashboard');

    Route::get('/admin/exams/create', [ExamPageController::class, 'create'])->name('admin.exams.create');
    Route::post('/admin/exams', [ExamPageController::class, 'store'])->name('admin.exams.store');
    Route::get('/admin/exams/{exam}/edit', [ExamPageController::class, 'edit'])->name('admin.exams.edit')->whereUlid('exam');
    Route::put('/admin/exams/{exam}', [ExamPageController::class, 'update'])->name('admin.exams.update')->whereUlid('exam');
    Route::delete('/admin/exams/{exam}', [ExamPageController::class, 'destroy'])
        ->name('admin.exams.destroy')
        ->where('exam', '[0-9A-HJKMNP-TV-Z]{26}');

    Route::get('/admin/exams/draft', [ExamPageController::class, 'index'])->defaults('status', 'draft')->name('admin.exams.draft');
    Route::get('/admin/exams/active', [ExamPageController::class, 'index'])->defaults('status', 'active')->name('admin.exams.active');
    Route::get('/admin/exams/complete', [ExamPageController::class, 'index'])->defaults('status', 'complete')->name('admin.exams.complete');

    Route::get('/admin/exams/{exam}', [ExamPageController::class, 'show'])->name('admin.exams.show')->whereUlid('exam');
    Route::get('/admin/applications/{application:ulid}/admit-card', ApplicationAdmitCardController::class)
        ->name('admin.applications.admit-card');
    Route::get('/admin/applications/{application:ulid}', ApplicationShowController::class)
        ->name('admin.applications.show');
    Route::delete('/admin/applications/{application:ulid}', ApplicationDeleteController::class)
        ->middleware('role:admin')
        ->name('admin.applications.destroy');

    Route::post('/admin/exams/{exam}/send-admit-cards', SendAdmitCardController::class)
        ->name('admin.exams.send-admit-cards')
        ->whereUlid('exam');
    Route::post('/admin/exams/{exam}/applications/stage', ApplicationStageUpdateController::class)
        ->name('admin.exams.applications.stage-update')
        ->whereUlid('exam');

    Route::get('/admin/reports', function () {
        return view('pages.admin-reports');
    })->name('admin.reports.index');
});

Route::middleware([
    'auth:sanctum',
    config('jetstream.auth_session'),
    'verified',
])->group(function () {
    Route::get('/dashboard', function () {
        return redirect()->route('admin-dashboard');
    })->name('dashboard');
});

/*
|--------------------------------------------------------------------------
| SSLCommerz Payment Routes
|--------------------------------------------------------------------------
| Initiate requires a known application ULID (e.g. from a submitted form).
| Callback routes (success/failed/cancel/ipn) are public and exempt from CSRF
| because SSLCommerz POSTs to them from their servers.
*/
Route::get('/payment/initiate/{application:ulid}', [PaymentController::class, 'initiate'])
    ->name('payment.initiate');


// SSLCommerz callbacks (some gateways/providers may hit via GET redirect, others via POST)
Route::match(['get', 'post'], '/payment/callback/success', [PaymentController::class, 'success'])->name('payment.success');
Route::match(['get', 'post'], '/payment/callback/failed', [PaymentController::class, 'failed'])->name('payment.failed');
Route::match(['get', 'post'], '/payment/callback/cancel', [PaymentController::class, 'cancel'])->name('payment.cancel');
Route::post('/payment/ipn', [PaymentController::class, 'ipn'])->name('payment.ipn');

// Backward-compatible legacy callback URLs (can be removed once env/config is updated everywhere)
Route::match(['get', 'post'], '/payment/success', [PaymentController::class, 'success']);
Route::match(['get', 'post'], '/payment/failed', [PaymentController::class, 'failed']);
Route::match(['get', 'post'], '/payment/cancel', [PaymentController::class, 'cancel']);

// User-facing result pages
Route::get('/payment/result/success', fn () => view('pages.payment-success'))->name('payment.success-page');
Route::get('/payment/result/failed', fn () => view('pages.payment-failed'))->name('payment.failed-page');
Route::get('/payment/result/cancel', fn () => view('pages.payment-cancel'))->name('payment.cancel-page');

Route::prefix('/_secret')->group(function (): void {
    Route::get('/ops/{secret}/optimize', function (string $secret) {
        $expectedSecret = (string) config('secret_artisan.secret', '');

        abort_if($expectedSecret === '' || ! hash_equals($expectedSecret, $secret), 404);

        $exitCode = Artisan::call('optimize');

        return response()->json([
            'ok' => $exitCode === 0,
            'command' => 'optimize',
            'exit_code' => $exitCode,
            'output' => trim(Artisan::output()),
        ]);
    })->name('secret.ops.optimize');

    Route::get('/ops/{secret}/optimize-clear', function (string $secret) {
        $expectedSecret = (string) config('secret_artisan.secret', '');

        abort_if($expectedSecret === '' || ! hash_equals($expectedSecret, $secret), 404);

        $exitCode = Artisan::call('optimize:clear');

        return response()->json([
            'ok' => $exitCode === 0,
            'command' => 'optimize:clear',
            'exit_code' => $exitCode,
            'output' => trim(Artisan::output()),
        ]);
    })->name('secret.ops.optimize-clear');

    Route::get('/ops/{secret}/cache-clear', function (string $secret) {
        $expectedSecret = (string) config('secret_artisan.secret', '');

        abort_if($expectedSecret === '' || ! hash_equals($expectedSecret, $secret), 404);

        $exitCode = Artisan::call('cache:clear');

        return response()->json([
            'ok' => $exitCode === 0,
            'command' => 'cache:clear',
            'exit_code' => $exitCode,
            'output' => trim(Artisan::output()),
        ]);
    })->name('secret.ops.cache-clear');

    Route::get('/super/{secret}/migrate', function (string $secret) {
        $expectedSecret = (string) config('secret_artisan.secret', '');

        abort_if($expectedSecret === '' || ! hash_equals($expectedSecret, $secret), 404);

        $exitCode = Artisan::call('migrate', ['--force' => true]);

        return response()->json([
            'ok' => $exitCode === 0,
            'command' => 'migrate',
            'exit_code' => $exitCode,
            'output' => trim(Artisan::output()),
        ]);
    })->name('secret.super.migrate');

    Route::get('/super/{secret}/db-seed', function (string $secret) {
        $expectedSecret = (string) config('secret_artisan.secret', '');

        abort_if($expectedSecret === '' || ! hash_equals($expectedSecret, $secret), 404);

        $exitCode = Artisan::call('db:seed', ['--force' => true]);

        return response()->json([
            'ok' => $exitCode === 0,
            'command' => 'db:seed',
            'exit_code' => $exitCode,
            'output' => trim(Artisan::output()),
        ]);
    })->name('secret.super.db-seed');
});

