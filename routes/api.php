<?php

use App\Http\Controllers\AuthController;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\DriverController;
use App\Http\Controllers\TripController;

Route::group(['prefix' => 'v1'], function () {
    Route::group(['prefix' => 'login'], function () {
        Route::post('/', [AuthController::class, 'login'])->name('login');
        Route::post('/verify', [AuthController::class, 'verify']);
        Route::get('/code', [AuthController::class, 'getVerificationCode']);
    });

    Route::middleware(['auth:sanctum'])->group(function () {
        Route::get('/me', [AuthController::class, 'getMe']);

        Route::group(['prefix' => 'driver'], function () {
            Route::get('/', [DriverController::class, 'show']);
            Route::post('/', [DriverController::class, 'store']);
        });

        Route::group(['prefix' => 'trips'], function () {
            Route::get('/{id}', [TripController::class, 'show']);
            Route::post('/', [TripController::class, 'store']);
            Route::post('/{id}/accept', [TripController::class, 'accept']);
            Route::post('/{id}/start', [TripController::class, 'start']);
            Route::post('/{id}/end', [TripController::class, 'end']);
            Route::post('/{id}/location', [TripController::class, 'location']);
        });
    });
});