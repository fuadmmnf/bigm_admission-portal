<?php

use App\Http\Controllers\Admin\ExamPageController;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\Payment\PaymentController;
use Illuminate\Support\Facades\Route;
use Livewire\Volt\Volt;

Route::get('/', HomeController::class)->name('home');

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

    Route::get('/admin/exams/draft', [ExamPageController::class, 'index'])->defaults('status', 'draft')->name('admin.exams.draft');
    Route::get('/admin/exams/active', [ExamPageController::class, 'index'])->defaults('status', 'active')->name('admin.exams.active');
    Route::get('/admin/exams/complete', [ExamPageController::class, 'index'])->defaults('status', 'complete')->name('admin.exams.complete');

    Route::get('/admin/exams/{exam}', [ExamPageController::class, 'show'])->name('admin.exams.show')->whereUlid('exam');

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

// SSLCommerz POST callbacks — CSRF excluded in bootstrap/app.php or via the controller
Route::post('/payment/success', [PaymentController::class, 'success'])->name('payment.success');
Route::post('/payment/failed', [PaymentController::class, 'failed'])->name('payment.failed');
Route::post('/payment/cancel', [PaymentController::class, 'cancel'])->name('payment.cancel');
Route::post('/payment/ipn', [PaymentController::class, 'ipn'])->name('payment.ipn');

// User-facing result pages
Route::get('/payment/success', fn () => view('pages.payment-success'))->name('payment.success-page');
Route::get('/payment/failed', fn () => view('pages.payment-failed'))->name('payment.failed-page');
Route::get('/payment/cancel', fn () => view('pages.payment-cancel'))->name('payment.cancel-page');

