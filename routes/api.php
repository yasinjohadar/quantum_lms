<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Admin\AnalyticsController;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
*/

Route::middleware(['auth:sanctum'])->group(function () {
    Route::prefix('analytics')->group(function() {
        Route::get('/student/{userId}', [AnalyticsController::class, 'student']);
        Route::get('/course/{subjectId}', [AnalyticsController::class, 'course']);
        Route::get('/system', [AnalyticsController::class, 'system']);
        Route::post('/track', [AnalyticsController::class, 'track']);
    });
});

