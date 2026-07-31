<?php

namespace Tests\Backup;

use App\Models\Backup;
use App\Services\Backup\BackupService;
use Illuminate\Support\Facades\DB;

/**
 * ملاحظة نطاق: restoreConfig()/restoreFull() تكتب فعلياً إلى base_path('.env') و
 * config_path('*.php') الحقيقية بتصميمها — تشغيلها تلقائياً في اختبارات CI غير آمن
 * (قد تُعدَّل ملفات المشروع الحقيقية بلا رجعة إن فشل التنظيف). سلوك restoreConfig()
 * (نسخ احتياطي لكل الملفات الأربعة قبل الاستبدال) والتراجع التعويضي في restoreFull()
 * تم التحقق منهما يدوياً بأمان (نسخ احتياطية يدوية مستقلة + استعادة فورية) أثناء بناء
 * هذه الميزة. هنا نختبر ما يمكن اختباره بأمان تلقائياً: حراسات restoreBackup()، وrestoreDatabase()
 * الفعلية عبر جدول اختبار مؤقت (مع تنظيف يدوي إجباري لأن DDL في MySQL يُنهي أي معاملة
 * ضمنياً فلا يعتمد على تراجع DatabaseTransactions).
 */
class BackupServiceRestoreTest extends BackupTestCase
{
    public function test_restore_backup_requires_completed_status(): void
    {
        $config = $this->makeStorageConfig();
        $backup = Backup::factory()->pending()->create(['storage_config_id' => $config->id]);

        $service = app(BackupService::class);

        $this->expectException(\RuntimeException::class);
        $service->restoreBackup($backup);
    }

    public function test_restore_backup_requires_storage_path(): void
    {
        $config = $this->makeStorageConfig();
        $backup = Backup::factory()->create([
            'storage_config_id' => $config->id,
            'status' => 'completed',
            'storage_path' => '',
        ]);

        $service = app(BackupService::class);

        $this->expectException(\RuntimeException::class);
        $service->restoreBackup($backup);
    }

    public function test_restore_database_applies_sql_statements(): void
    {
        $tableName = 'qa_restore_test_' . uniqid();
        $sqlFile = tempnam(sys_get_temp_dir(), 'qa_sql_');

        file_put_contents($sqlFile, "
            DROP TABLE IF EXISTS `{$tableName}`;
            CREATE TABLE `{$tableName}` (id INT PRIMARY KEY, val VARCHAR(50));
            INSERT INTO `{$tableName}` (id, val) VALUES (1, 'قصد الاختبار');
        ");

        $service = app(BackupService::class);
        $ref = new \ReflectionClass($service);
        $method = $ref->getMethod('restoreDatabase');
        $method->setAccessible(true);

        try {
            $method->invoke($service, $sqlFile);

            $row = DB::table($tableName)->where('id', 1)->first();
            $this->assertNotNull($row);
            $this->assertSame('قصد الاختبار', $row->val);
        } finally {
            // تنظيف يدوي إجباري — DDL (CREATE/DROP TABLE) يُنهي أي معاملة MySQL
            // ضمنياً، فلا يمكن الاعتماد على تراجع DatabaseTransactions لحذف الجدول.
            DB::statement("DROP TABLE IF EXISTS `{$tableName}`");
            @unlink($sqlFile);
        }
    }

    /**
     * لا نستدعي restoreFull() الحقيقية هنا عمداً: هي تأخذ لقطة لقاعدة البيانات
     * *بأكملها* عبر writeDatabaseDump() الحقيقي، فتنفيذها في اختبار تلقائي يعني
     * تفريغ واستعادة قاعدة بيانات المشروع الحقيقية المشتركة عند كل تشغيل — ثقيل
     * وغير آمن. بدلاً من ذلك نتحقق من أن *آلية* التراجع (إعادة تطبيق restoreDatabase()
     * بلقطة سابقة) تعمل فعلياً وتعيد الحالة الأصلية بدقة — وهي نفس الآلية المستخدمة
     * حرفياً داخل catch block الخاص بـ restoreFull() (راجع BackupService::restoreFull).
     */
    public function test_restore_database_rollback_mechanism_restores_original_state(): void
    {
        $tableName = 'qa_restore_full_' . uniqid();

        $preRestoreSql = tempnam(sys_get_temp_dir(), 'qa_pre_');
        file_put_contents($preRestoreSql, "
            DROP TABLE IF EXISTS `{$tableName}`;
            CREATE TABLE `{$tableName}` (id INT PRIMARY KEY, val VARCHAR(50));
            INSERT INTO `{$tableName}` (id, val) VALUES (1, 'original');
        ");

        $newSql = tempnam(sys_get_temp_dir(), 'qa_new_');
        file_put_contents($newSql, "
            DELETE FROM `{$tableName}` WHERE id = 1;
            INSERT INTO `{$tableName}` (id, val) VALUES (1, 'restored-but-should-be-rolled-back');
        ");

        $service = app(BackupService::class);
        $ref = new \ReflectionClass($service);
        $restoreDatabaseMethod = $ref->getMethod('restoreDatabase');
        $restoreDatabaseMethod->setAccessible(true);

        // ابنِ الحالة "الأصلية" فعلياً في الجدول (تمثّل ما ستأخذه writeDatabaseDump كلقطة)
        $restoreDatabaseMethod->invoke($service, $preRestoreSql);

        // طبّق استعادة "جديدة" تغيّر البيانات (تمثّل استعادة قاعدة البيانات الناجحة
        // داخل restoreFull() قبل أن تفشل مرحلة الملفات/الإعدادات اللاحقة)
        $restoreDatabaseMethod->invoke($service, $newSql);
        $afterNewRestore = DB::table($tableName)->where('id', 1)->first();
        $this->assertSame('restored-but-should-be-rolled-back', $afterNewRestore->val);

        try {
            // محاكاة التراجع التعويضي كما يفعل restoreFull() عند فشل مرحلة لاحقة
            $restoreDatabaseMethod->invoke($service, $preRestoreSql);

            $afterRollback = DB::table($tableName)->where('id', 1)->first();
            $this->assertSame('original', $afterRollback->val, 'التراجع التعويضي يجب أن يعيد القيمة الأصلية.');
        } finally {
            DB::statement("DROP TABLE IF EXISTS `{$tableName}`");
            @unlink($preRestoreSql);
            @unlink($newSql);
        }
    }
}
