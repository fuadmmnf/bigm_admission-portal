<?php

use App\Http\Controllers\Payment\PaymentController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
});

Route::get('/admin/login', function () {
    return view('pages.admin-login');
})->name('admin-login')->middleware('guest');

Route::middleware([
    'auth:sanctum',
    config('jetstream.auth_session'),
    'verified',
])->group(function () {
    Route::get('/admin/dashboard', function () {
        return view('pages.admin-dashboard');
    })->name('admin-dashboard')->middleware('role:admin|moderator');
});

Route::middleware([
    'auth:sanctum',
    config('jetstream.auth_session'),
    'verified',
])->group(function () {
    Route::get('/dashboard', function () {
        return view('dashboard');
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

