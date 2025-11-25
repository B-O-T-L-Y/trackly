<?php

use App\Http\Controllers\Api\V1\EventController;
use App\Http\Controllers\Api\V1\StatsController;
use Illuminate\Support\Facades\Route;

Route::prefix('v1')->group(function () {
    Route::post('/events', [EventController::class, 'store']);
    Route::get('/stats', [StatsController::class, 'today']);
});
