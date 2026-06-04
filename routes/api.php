<?php

use App\Http\Controllers\API\WhatsAppWebhookController;
use Illuminate\Support\Facades\Route;

Route::prefix('webhooks/whatsapp')
    ->name('webhooks.whatsapp.')
    ->middleware(['throttle:60,1'])
    ->group(function () {
        Route::get('/', [WhatsAppWebhookController::class, 'verify'])->name('verify');
        Route::post('/', [WhatsAppWebhookController::class, 'handle'])->name('handle');
    });

// مسارات إضافة Chrome: routes/extension-api.php (تُحمَّل من web.php)
