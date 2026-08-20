<?php

use App\Http\Controllers\Dev\DevLoginController;
use App\Support\DevLogin;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| مسارات بيئة التطوير فقط
|--------------------------------------------------------------------------
|
| لا تُسجَّل هذه المسارات إطلاقاً خارج بيئة التطوير (تعيد DevLogin::enabled()
| القيمة false في الإنتاج)، لذلك تعود بـ 404 على السيرفر الحقيقي.
|
*/

if (! DevLogin::enabled()) {
    return;
}

Route::prefix('dev')->as('dev.')->group(function () {
    // لوحة الحسابات التجريبية: /dev/login
    Route::get('login', [DevLoginController::class, 'index'])->name('login');

    // دخول فوري بحساب محدد: /dev/login/admin
    Route::get('login/{key}', [DevLoginController::class, 'loginAs'])->name('login.as');

    // إنشاء/تحديث الحسابات التجريبية من المتصفح
    Route::post('seed', [DevLoginController::class, 'seed'])->name('seed');
});
