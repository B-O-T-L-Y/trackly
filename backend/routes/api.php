<?php

use App\Http\Controllers\Api\V1\EventController;
use App\Http\Controllers\Api\V1\StatsController;
use Illuminate\Support\Facades\Route;

if (app()->environment('local')) {
    Route::prefix('dev')->group(function () {
        Route::get('/token', function () {
            return response()->json([
                'token' => config('tracking.token'),
            ]);
        });
    });
}

Route::middleware(['tracking.auth'])
    ->prefix('v1')
    ->group(function () {
        Route::post('/events', [EventController::class, 'store']);

        Route::prefix('stats')->group(function () {
            Route::get('/today', [StatsController::class, 'today']);
        });
    });
