<?php

use App\InteractiveLearning\Http\Controllers\Admin\LearningExperienceController;
use App\InteractiveLearning\Http\Controllers\LearningExperiencePlayController;
use Illuminate\Support\Facades\Route;

Route::middleware(['auth', 'check.user.active'])->group(function () {
    Route::get('/learning-experiences/tts', [LearningExperiencePlayController::class, 'tts'])
        ->name('learning-experiences.tts');

    Route::get('/learning-experiences/{learningExperience}', [LearningExperiencePlayController::class, 'show'])
        ->name('learning-experiences.show');

    Route::post('/learning-experiences/{learningExperience}/attempts', [LearningExperiencePlayController::class, 'storeAttempt'])
        ->name('learning-experiences.attempts.store');
});
