<?php

use App\Http\Controllers\API\WhatsAppWebhookController;
use App\Http\Controllers\Api\Extension\ExtensionAuthController;
use App\Http\Controllers\Api\Extension\ExtensionCurriculumController;
use App\Http\Controllers\Api\Extension\ExtensionQuestionImportController;
use Illuminate\Support\Facades\Route;

Route::prefix('webhooks/whatsapp')
    ->name('webhooks.whatsapp.')
    ->middleware(['throttle:60,1'])
    ->group(function () {
        Route::get('/', [WhatsAppWebhookController::class, 'verify'])->name('verify');
        Route::post('/', [WhatsAppWebhookController::class, 'handle'])->name('handle');
    });

Route::prefix('v1/extension')
    ->name('extension.')
    ->group(function () {
        Route::post('auth/login', [ExtensionAuthController::class, 'login'])
            ->middleware('throttle:10,1')
            ->name('auth.login');

        Route::middleware(['auth:sanctum', 'extension.api'])->group(function () {
            Route::get('auth/me', [ExtensionAuthController::class, 'me'])->name('auth.me');
            Route::post('auth/logout', [ExtensionAuthController::class, 'logout'])->name('auth.logout');

            Route::get('curriculum/classes', [ExtensionCurriculumController::class, 'classes'])
                ->name('curriculum.classes');
            Route::get('curriculum/subjects', [ExtensionCurriculumController::class, 'subjects'])
                ->name('curriculum.subjects');
            Route::get('curriculum/units', [ExtensionCurriculumController::class, 'units'])
                ->name('curriculum.units');

            Route::post('questions/import', [ExtensionQuestionImportController::class, 'import'])
                ->middleware('throttle:30,1')
                ->name('questions.import');
        });
    });
