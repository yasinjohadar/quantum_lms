<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

// جدولة النسخ الاحتياطية
Schedule::command('backup:run-scheduled')->everyMinute();
Schedule::command('backup:cleanup-expired')->daily();

// حذف حسابات التسجيل غير المُفعّلة بعد انتهاء صلاحية كل أكواد التحقق
Schedule::command('users:prune-unverified-phone-registrations')->everyTenMinutes();

// إلغاء اشتراكات الصفوف والمواد المنتهية تلقائياً
Schedule::command('purchases:expire-access')->hourly();
Schedule::command('classes:expire-subscriptions')->hourly();
