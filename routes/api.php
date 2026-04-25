<?php

use App\Http\Controllers\Api\CategoryIndexController;
use App\Http\Controllers\Api\UserIndexController;
use App\Http\Controllers\Api\ExamIndexController;
use App\Http\Controllers\Api\ApplicationIndexController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');

Route::get('/categories', CategoryIndexController::class)->name('api.categories.index');

Route::middleware(['auth:sanctum', 'role:admin|moderator'])->group(function (): void {
    Route::get('/exams', ExamIndexController::class)->name('api.exams.index');
    Route::get('/applications', ApplicationIndexController::class)->name('api.applications.index');
});

Route::middleware(['auth:sanctum', 'permission:users.view'])->group(function (): void {
    Route::get('/users', UserIndexController::class)->name('api.users.index');
});

