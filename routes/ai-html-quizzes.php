<?php

use App\AiHtmlQuiz\Http\Controllers\AiHtmlQuizPlayController;
use Illuminate\Support\Facades\Route;

Route::middleware(['auth', 'check.user.active'])->group(function () {
    Route::get('/ai-html-quizzes/{aiHtmlQuiz}', [AiHtmlQuizPlayController::class, 'show'])
        ->name('ai-html-quizzes.show');

    Route::get('/ai-html-quizzes/{aiHtmlQuiz}/bundle', [AiHtmlQuizPlayController::class, 'bundle'])
        ->name('ai-html-quizzes.bundle');

    Route::post('/ai-html-quizzes/{aiHtmlQuiz}/attempts', [AiHtmlQuizPlayController::class, 'storeAttempt'])
        ->name('ai-html-quizzes.attempts.store');
});
