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
