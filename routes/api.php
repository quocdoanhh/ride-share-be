<?php

use App\Http\Controllers\AuthController;
use Illuminate\Support\Facades\Route;

Route::group(['prefix' => 'v1'], function () {
    Route::group(['prefix' => 'login'], function () {
        Route::post('/', [AuthController::class, 'login'])->name('login');
        Route::post('/verify', [AuthController::class, 'verify']);
    });

    Route::middleware(['auth:sanctum'])->group(function () {
        Route::get('/me', [AuthController::class, 'getMe']);
    });
});