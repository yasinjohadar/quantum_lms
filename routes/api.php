<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\API\WhatsAppWebhookController;

Route::prefix('webhooks/whatsapp')
    ->name('webhooks.whatsapp.')
    ->middleware(['throttle:60,1'])
    ->group(function () {
        Route::get('/', [WhatsAppWebhookController::class, 'verify'])->name('verify');
        Route::post('/', [WhatsAppWebhookController::class, 'handle'])->name('handle');
    });


