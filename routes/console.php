<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

// جدولة النسخ الاحتياطية
Schedule::command('backup:run-scheduled')
    ->everyMinute()
    ->withoutOverlapping(120);
Schedule::command('backup:cleanup-expired')
    ->daily()
    ->withoutOverlapping();

// حذف حسابات التسجيل غير المُفعّلة بعد انتهاء صلاحية كل أكواد التحقق
Schedule::command('users:prune-unverified-phone-registrations')->everyTenMinutes();

// إلغاء اشتراكات الصفوف والمواد المنتهية تلقائياً
Schedule::command('purchases:expire-access')->hourly();
Schedule::command('classes:expire-subscriptions')->hourly();

// نظام التحفيز: إعادة تهيئة المهام اليومية/الأسبوعية تلقائياً
// (بدونها تبقى المهام المُنجَزة مكتملة للأبد ولا تُجدَّد)
Schedule::command('tasks:reset-daily')->dailyAt('00:05')->withoutOverlapping();
Schedule::command('tasks:reset-weekly')->weeklyOn(6, '00:10')->withoutOverlapping();
