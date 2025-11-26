<?php

use App\Http\Controllers\Api\V1\EventController;
use App\Http\Controllers\Api\V1\StatsController;
use Illuminate\Support\Facades\Route;

Route::middleware(['tracking.auth'])
    ->prefix('v1')
    ->group(function () {
        Route::post('/events', [EventController::class, 'store']);

        Route::prefix('stats')->group(function () {
            Route::get('/today', [StatsController::class, 'today']);
        });
    });
