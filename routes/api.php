<?php

use App\Http\Controllers\Api\CategoryIndexController;
use App\Http\Controllers\Api\UserIndexController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');

Route::get('/categories', CategoryIndexController::class)->name('api.categories.index');

Route::middleware(['auth:sanctum', 'permission:users.view'])->group(function (): void {
    Route::get('/users', UserIndexController::class)->name('api.users.index');
});

