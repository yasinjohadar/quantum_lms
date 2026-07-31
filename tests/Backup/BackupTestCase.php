<?php

namespace Tests\Backup;

use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

/**
 * قاعدة مشتركة لاختبارات نظام النسخ الاحتياطي. تستخدم DatabaseTransactions
 * (وليس RefreshDatabase) لأن بعض هجرات المشروع تحتوي SQL خاص بـ MySQL غير
 * متوافق مع sqlite (بيئة الاختبار الافتراضية في phpunit.xml) — نفس النمط
 * المتّبع في tests/Curriculum. شغّل هذه الاختبارات فعلياً عبر:
 *
 *   DB_CONNECTION=mysql DB_HOST=127.0.0.1 DB_PORT=3307 DB_DATABASE=quantum \
 *   DB_USERNAME=root DB_PASSWORD=root php artisan test tests/Backup
 *
 * تحت sqlite الافتراضي تُتخطّى الاختبارات بأمان (markTestSkipped) بدل الفشل.
 */
abstract class BackupTestCase extends TestCase
{
    use DatabaseTransactions;

    protected function setUp(): void
    {
        parent::setUp();

        if (DB::connection()->getDriverName() === 'sqlite') {
            $this->markTestSkipped('SQLite غير متوافق مع بعض الهجرات — شغّل عبر اتصال MySQL حقيقي (راجع تعليق الصنف).');
        }
    }

    protected function makeStorageConfig(string $driver = 'local'): \App\Models\AppStorageConfig
    {
        return \App\Models\AppStorageConfig::create([
            'name' => 'test-' . $driver . '-' . uniqid(),
            'driver' => $driver,
            'config' => ['path' => 'public'],
            'is_active' => true,
            'priority' => 0,
            'redundancy' => false,
        ]);
    }
}
