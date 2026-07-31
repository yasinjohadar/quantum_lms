<?php

namespace App\Services\Backup;

use App\Models\Backup;
use App\Models\BackupLog;
use App\Services\Backup\BackupStorageService;
use App\Services\Backup\BackupCompressionService;
use App\Services\Backup\BackupNotificationService;
use App\Services\Backup\StorageManager;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Log;
use Carbon\Carbon;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

class BackupService
{
    public function __construct(
        private BackupStorageService $storageService,
        private BackupCompressionService $compressionService,
        private BackupNotificationService $notificationService,
        private StorageManager $storageManager
    ) {}

    /**
     * إنشاء سجل معلّق ثم معالجته فوراً (متزامن — للاختبارات/الأوامر).
     */
    public function createBackup(array $options): Backup
    {
        $backup = $this->createPendingBackup($options);

        return $this->processBackup($backup, $options);
    }

    /**
     * إنشاء سجل معلّق وإرساله للطابور.
     */
    public function queueBackup(array $options): Backup
    {
        $backup = $this->createPendingBackup($options);

        \App\Jobs\CreateBackupJob::dispatch($backup, $options);

        $this->log($backup, 'info', 'تم إرسال مهمة إنشاء النسخة إلى الطابور');

        return $backup->fresh();
    }

    /**
     * إنشاء سجل النسخة بحالة pending دون تنفيذ العمل الثقيل.
     */
    public function createPendingBackup(array $options): Backup
    {
        $storageConfigId = $options['storage_config_id'] ?? null;

        if (! $storageConfigId) {
            throw new \InvalidArgumentException('storage_config_id مطلوب لإنشاء نسخة احتياطية.');
        }

        $backup = Backup::create([
            'name' => $options['name'] ?? 'backup_' . now()->format('Y-m-d_H-i-s'),
            'type' => $options['type'] ?? 'manual',
            'backup_type' => $options['backup_type'] ?? 'full',
            'storage_config_id' => $storageConfigId,
            'storage_path' => $options['storage_path'] ?? '',
            'file_path' => $options['file_path'] ?? '',
            'compression_type' => $options['compression_type'] ?? 'zip',
            'status' => 'pending',
            'retention_days' => $options['retention_days'] ?? 30,
            'created_by' => $options['created_by'] ?? auth()->id(),
            'backup_schedule_id' => $options['backup_schedule_id']
                ?? $options['schedule_id']
                ?? null,
        ]);

        $backup->update([
            'expires_at' => $backup->calculateExpiresAt(),
        ]);

        return $backup->fresh();
    }

    /**
     * تنفيذ عمل النسخ على سجل موجود (يُستدعى من الـ Job عادة).
     */
    public function processBackup(Backup $backup, array $options = []): Backup
    {
        $backup->refresh();

        if ($backup->status === 'completed') {
            return $backup;
        }

        if ($backup->status === 'running') {
            $this->log($backup, 'warning', 'تم تخطي المعالجة لأن النسخة قيد التنفيذ بالفعل');

            return $backup;
        }

        if ($backup->status === 'failed') {
            throw new \RuntimeException('لا يمكن إعادة معالجة نسخة فاشلة. أنشئ نسخة جديدة.');
        }

        $backup->update([
            'started_at' => now(),
            'status' => 'running',
            'error_message' => null,
            'completed_at' => null,
        ]);

        $sourcePath = null;
        $compressedPath = null;

        try {
            $this->log($backup, 'info', 'بدء عملية النسخ الاحتياطي');

            $sourcePath = match ($backup->backup_type) {
                'full' => $this->createFullBackup($backup, $options),
                'database' => $this->createDatabaseBackup($backup, $options),
                'files' => $this->createFilesBackup($backup, $options),
                'config' => $this->createConfigBackup($backup, $options),
                default => throw new \Exception('نوع النسخ غير معروف'),
            };

            $backup->update(['file_path' => $sourcePath]);

            // ملاحظة أمنية: $compressedPath غير مشفّر عند تخزينه (انظر BackupCompressionService)
            // — قد يتضمن .env الحقيقي لنوع full/config. تشفير AES-256 لاحق مؤجَّل بقرار صريح.
            $compressedPath = $this->compressionService->compress($backup, $backup->compression_type);

            $this->storageManager->storeWithFailover($backup, $compressedPath);
            $this->storageManager->storeToMultipleStorages($backup, $compressedPath);

            $storagePath = $backup->fresh()->storage_path;

            if (! file_exists($compressedPath)) {
                throw new \Exception('ملف النسخة الاحتياطية غير موجود: ' . $compressedPath);
            }

            $fileSize = filesize($compressedPath);
            if ($fileSize === false) {
                throw new \Exception('فشل في الحصول على حجم ملف النسخة الاحتياطية: ' . $compressedPath);
            }

            $duration = now()->diffInSeconds($backup->started_at);

            $backup->update([
                'status' => 'completed',
                'completed_at' => now(),
                'duration' => $duration,
                'file_path' => $compressedPath,
                'storage_path' => $storagePath,
                'file_size' => $fileSize,
            ]);

            $this->log($backup, 'info', 'اكتملت عملية النسخ الاحتياطي بنجاح');
            $this->notificationService->notifyBackupCompleted($backup->fresh());

            return $backup->fresh();
        } catch (\Exception $e) {
            $backup->update([
                'status' => 'failed',
                'completed_at' => now(),
                'error_message' => $e->getMessage(),
            ]);

            $this->log($backup, 'error', 'فشلت عملية النسخ الاحتياطي: ' . $e->getMessage());
            $this->notificationService->notifyBackupFailed($backup->fresh(), $e->getMessage());

            throw $e;
        } finally {
            $this->cleanupLocalBackupArtifacts($backup, $sourcePath, $compressedPath);
        }
    }

    /**
     * إنشاء نسخة كاملة بهيكل موحّد:
     * database.sql + files/ + config/
     */
    public function createFullBackup(Backup $backup, array $options): string
    {
        $backupDir = storage_path('app/backups/temp/' . $backup->id);
        $this->ensureDirectory($backupDir);
        $this->ensureDirectory($backupDir . DIRECTORY_SEPARATOR . 'files');
        $this->ensureDirectory($backupDir . DIRECTORY_SEPARATOR . 'config');

        $this->log($backup, 'info', 'بدء نسخ قاعدة البيانات');
        $dbPath = $backupDir . DIRECTORY_SEPARATOR . 'database.sql';
        $this->writeDatabaseDump($backup, $dbPath);

        $this->log($backup, 'info', 'بدء نسخ الملفات');
        $filesSource = storage_path('app/public');
        if (is_dir($filesSource)) {
            $this->copyDirectory($filesSource, $backupDir . DIRECTORY_SEPARATOR . 'files');
        }

        $this->log($backup, 'info', 'بدء نسخ الإعدادات');
        $this->writeConfigFiles($backupDir . DIRECTORY_SEPARATOR . 'config');

        $this->log($backup, 'info', 'تم تجميع النسخة الكاملة بنجاح');

        return $backupDir;
    }

    /**
     * إنشاء نسخة قاعدة البيانات
     */
    public function createDatabaseBackup(Backup $backup, array $options): string
    {
        $stagingDir = storage_path('app/backups/temp/database_' . $backup->id);
        $this->ensureDirectory($stagingDir);

        $path = $stagingDir . DIRECTORY_SEPARATOR . 'database.sql';
        $this->writeDatabaseDump($backup, $path);

        $this->log($backup, 'info', 'تم نسخ قاعدة البيانات بنجاح');

        return $stagingDir;
    }

    /**
     * إنشاء نسخة الملفات
     */
    public function createFilesBackup(Backup $backup, array $options): string
    {
        $backupDir = storage_path('app/backups/temp/files_' . $backup->id);
        $this->ensureDirectory($backupDir);

        $filesDir = storage_path('app/public');
        if (is_dir($filesDir)) {
            $this->copyDirectory($filesDir, $backupDir);
        }

        $this->log($backup, 'info', 'تم نسخ الملفات بنجاح');

        return $backupDir;
    }

    /**
     * إنشاء نسخة الإعدادات
     */
    public function createConfigBackup(Backup $backup, array $options): string
    {
        $backupDir = storage_path('app/backups/temp/config_' . $backup->id);
        $this->ensureDirectory($backupDir);
        $this->writeConfigFiles($backupDir);

        $this->log($backup, 'info', 'تم نسخ الإعدادات بنجاح');

        return $backupDir;
    }

    /**
     * كتابة dump SQL إلى مسار محدد.
     */
    private function writeDatabaseDump(Backup $backup, string $path): void
    {
        $this->ensureDirectory(dirname($path));

        $database = config('database.connections.mysql.database');

        try {
            $tables = DB::select('SHOW TABLES');
            $tablesKey = 'Tables_in_' . $database;

            $excludedTables = array_flip((array) config('backup.excluded_tables', []));

            $sqlContent = "-- Database Backup\n";
            $sqlContent .= '-- Generated: ' . now()->toDateTimeString() . "\n";
            $sqlContent .= "-- Database: {$database}\n";
            $sqlContent .= "-- Excluded tables (never touched by restore): " . implode(', ', array_keys($excludedTables)) . "\n\n";
            $sqlContent .= "SET FOREIGN_KEY_CHECKS=0;\n\n";

            foreach ($tables as $table) {
                $tableName = $table->$tablesKey;

                // جداول تتبّع النسخ الاحتياطية والحالة العابرة (جلسات/طابور/كاش)
                // تُستبعد كلياً — راجع تعليق config('backup.excluded_tables').
                if (isset($excludedTables[$tableName])) {
                    continue;
                }

                $createTable = DB::select("SHOW CREATE TABLE `{$tableName}`");
                $sqlContent .= "DROP TABLE IF EXISTS `{$tableName}`;\n";
                $sqlContent .= $createTable[0]->{'Create Table'} . ";\n\n";

                $rows = DB::table($tableName)->get();
                if ($rows->count() === 0) {
                    continue;
                }

                $sqlContent .= "LOCK TABLES `{$tableName}` WRITE;\n";

                $firstRow = (array) $rows->first();
                $columns = array_map(static fn ($col) => "`{$col}`", array_keys($firstRow));
                $columnsStr = implode(', ', $columns);

                $values = [];
                $currentChunk = 0;
                $currentChunkBytes = 0;
                $chunkSize = (int) config('backup.sql_dump_chunk_size', 100);
                $maxStatementBytes = (int) config('backup.sql_dump_max_statement_bytes', 512 * 1024);

                foreach ($rows as $row) {
                    $rowArray = (array) $row;
                    $valArray = array_map(static function ($val) {
                        if ($val === null) {
                            return 'NULL';
                        }

                        return DB::getPdo()->quote($val);
                    }, array_values($rowArray));

                    $tuple = '(' . implode(', ', $valArray) . ')';

                    // إنهاء الدفعة الحالية قبل إضافة الصف الجديد إذا كانت الإضافة
                    // ستتجاوز الحد الأقصى للبايتات — يمنع عبارة INSERT ضخمة تتجاوز
                    // max_allowed_packet في MySQL بسبب عمود نصي كبير في صف واحد.
                    if ($values !== [] && ($currentChunk >= $chunkSize || $currentChunkBytes + strlen($tuple) > $maxStatementBytes)) {
                        $sqlContent .= "INSERT INTO `{$tableName}` ({$columnsStr}) VALUES\n" . implode(",\n", $values) . ";\n\n";
                        $values = [];
                        $currentChunk = 0;
                        $currentChunkBytes = 0;
                    }

                    $values[] = $tuple;
                    $currentChunk++;
                    $currentChunkBytes += strlen($tuple);
                }

                if (! empty($values)) {
                    $sqlContent .= "INSERT INTO `{$tableName}` ({$columnsStr}) VALUES\n" . implode(",\n", $values) . ";\n\n";
                }

                $sqlContent .= "UNLOCK TABLES;\n\n";
            }

            $sqlContent .= "SET FOREIGN_KEY_CHECKS=1;\n";

            if (file_put_contents($path, $sqlContent) === false) {
                throw new \Exception('تعذر كتابة ملف قاعدة البيانات');
            }

            if (! file_exists($path) || filesize($path) === 0) {
                throw new \Exception('فشل في إنشاء ملف النسخة الاحتياطية - الملف فارغ أو غير موجود');
            }
        } catch (\Exception $e) {
            Log::error('Database backup failed: ' . $e->getMessage(), [
                'backup_id' => $backup->id,
                'trace' => $e->getTraceAsString(),
            ]);
            throw new \Exception('فشل في نسخ قاعدة البيانات: ' . $e->getMessage());
        }
    }

    /**
     * كتابة ملفات الإعدادات بهيكل قابل للاستعادة لاحقاً.
     */
    private function writeConfigFiles(string $destinationDir): void
    {
        $this->ensureDirectory($destinationDir);

        $configFiles = [
            '.env' => '.env',
            'config/app.php' => 'app.php',
            'config/database.php' => 'database.php',
            'config/mail.php' => 'mail.php',
        ];

        foreach ($configFiles as $relativeSource => $relativeDest) {
            $sourcePath = base_path($relativeSource);
            if (! file_exists($sourcePath)) {
                continue;
            }

            $destPath = $destinationDir . DIRECTORY_SEPARATOR . $relativeDest;
            $this->ensureDirectory(dirname($destPath));
            copy($sourcePath, $destPath);
        }
    }

    /**
     * حذف نسخة
     */
    public function deleteBackup(Backup $backup): bool
    {
        try {
            // حذف الملف من التخزين
            $this->storageService->deleteBackupFromStorage($backup);

            // حذف الملف المحلي - file_path هو مسار كامل (absolute path)
            if ($backup->file_path && file_exists($backup->file_path)) {
                @unlink($backup->file_path);
            }

            $backup->delete();

            return true;
        } catch (\Exception $e) {
            Log::error('Error deleting backup: ' . $e->getMessage(), [
                'backup_id' => $backup->id,
                'trace' => $e->getTraceAsString(),
            ]);
            throw new \Exception('فشل في حذف النسخة: ' . $e->getMessage());
        }
    }

    /**
     * تحميل نسخة من التخزين مع حذف الملف المؤقت بعد الإرسال.
     */
    public function downloadBackup(Backup $backup): BinaryFileResponse
    {
        if ($backup->status !== 'completed') {
            throw new \RuntimeException('يمكن تحميل النسخ المكتملة فقط.');
        }

        if (! $backup->storage_path) {
            throw new \RuntimeException('مسار التخزين غير موجود لهذه النسخة.');
        }

        $extension = $this->resolveArchiveExtension($backup);
        $tempFilePath = storage_path(
            'app/temp/download_' . $backup->id . '_' . uniqid('', true) . '.' . $extension
        );

        $this->ensureDirectory(dirname($tempFilePath));

        $fileContent = $this->storageManager->retrieve($backup);
        if ($fileContent === '' || $fileContent === false) {
            throw new \RuntimeException('تعذر جلب ملف النسخة من التخزين.');
        }

        if (file_put_contents($tempFilePath, $fileContent) === false) {
            throw new \RuntimeException('تعذر تجهيز ملف التحميل المؤقت.');
        }

        if (! file_exists($tempFilePath) || filesize($tempFilePath) === 0) {
            @unlink($tempFilePath);
            throw new \RuntimeException('ملف التحميل فارغ أو غير موجود.');
        }

        $downloadName = preg_replace('/[^\w\-\.\p{Arabic}]+/u', '_', $backup->name) . '.' . $extension;

        return response()
            ->download($tempFilePath, $downloadName)
            ->deleteFileAfterSend(true);
    }

    /**
     * استعادة نسخة مكتملة فقط، وفق الهيكل الموحّد:
     * - full: database.sql + files/ + config/
     * - database: database.sql
     * - files: محتويات الملفات
     * - config: .env + app.php + database.php + mail.php
     */
    public function restoreBackup(Backup $backup, array $options = []): bool
    {
        if ($backup->status !== 'completed') {
            throw new \RuntimeException('يمكن استعادة النسخ المكتملة فقط.');
        }

        if (! $backup->storage_path) {
            throw new \RuntimeException('مسار التخزين غير موجود لهذه النسخة.');
        }

        $tempArchive = null;
        $extractDir = storage_path('app/backups/temp/restore_' . $backup->id . '_' . uniqid());

        try {
            $this->log($backup, 'info', 'بدء عملية الاستعادة');

            $extension = $this->resolveArchiveExtension($backup);
            $tempArchive = storage_path('app/temp/restore_' . $backup->id . '_' . uniqid('', true) . '.' . $extension);
            $this->ensureDirectory(dirname($tempArchive));
            $this->ensureDirectory($extractDir);

            $fileContent = $this->storageManager->retrieve($backup);
            if ($fileContent === '' || $fileContent === false) {
                throw new \RuntimeException('تعذر جلب ملف النسخة من التخزين.');
            }

            if (file_put_contents($tempArchive, $fileContent) === false) {
                throw new \RuntimeException('تعذر حفظ ملف النسخة المؤقت للاستعادة.');
            }

            $extractedPath = $this->compressionService->decompress($tempArchive, $extractDir);
            $paths = $this->resolveRestoreContentPaths($extractedPath, $backup->backup_type);

            match ($backup->backup_type) {
                'database' => $this->restoreDatabase($paths['database']),
                'files' => $this->restoreFiles($paths['files']),
                'config' => $this->restoreConfig($paths['config']),
                'full' => $this->restoreFull($backup, $paths),
                default => throw new \RuntimeException('نوع النسخ غير معروف'),
            };

            $this->log($backup, 'info', 'اكتملت عملية الاستعادة بنجاح');

            return true;
        } catch (\Exception $e) {
            $this->log($backup, 'error', 'فشلت عملية الاستعادة: ' . $e->getMessage());
            throw $e;
        } finally {
            if ($tempArchive) {
                $this->deletePath($tempArchive);
            }
            $this->deletePath($extractDir);
        }
    }

    /**
     * تنظيف النسخ المنتهية الصلاحية على دفعات (chunkById) بدل تحميلها كلها دفعة
     * واحدة، مع تجميع ملخّص كامل بدل مجرد عدد ناجح. $dryRun=true يُعاين فقط
     * (بدون حذف فعلي) لمعرفة ما سيُحذف قبل التنفيذ الحقيقي.
     *
     * @return array{deleted: int, failed: int, total_bytes_freed: int, failed_ids: list<array{id: int, reason: string}>}
     */
    public function cleanupExpiredBackups(bool $dryRun = false): array
    {
        $summary = [
            'deleted' => 0,
            'failed' => 0,
            'total_bytes_freed' => 0,
            'failed_ids' => [],
        ];

        Backup::expired()->chunkById(100, function ($backups) use (&$summary, $dryRun) {
            foreach ($backups as $backup) {
                $fileSize = (int) $backup->file_size;

                if ($dryRun) {
                    $summary['deleted']++;
                    $summary['total_bytes_freed'] += $fileSize;
                    continue;
                }

                try {
                    $this->deleteBackup($backup);
                    $summary['deleted']++;
                    $summary['total_bytes_freed'] += $fileSize;
                } catch (\Exception $e) {
                    $summary['failed']++;
                    $summary['failed_ids'][] = ['id' => $backup->id, 'reason' => $e->getMessage()];
                    Log::error('Error deleting expired backup: ' . $e->getMessage(), ['backup_id' => $backup->id]);
                }
            }
        });

        return $summary;
    }

    /**
     * الحصول على حجم النسخة
     */
    public function getBackupSize(Backup $backup): int
    {
        return $backup->file_size ?? 0;
    }

    /**
     * الحصول على إجمالي حجم النسخ
     */
    public function getTotalBackupSize(): int
    {
        return Backup::completed()->sum('file_size');
    }

    /**
     * الحصول على إحصائيات النسخ
     */
    public function getBackupStats(): array
    {
        return [
            'total' => Backup::count(),
            'completed' => Backup::completed()->count(),
            'failed' => Backup::failed()->count(),
            'pending' => Backup::where('status', 'pending')->count(),
            'running' => Backup::where('status', 'running')->count(),
            'total_size' => $this->getTotalBackupSize(),
            'expired' => Backup::expired()->count(),
            'stuck' => $this->countStuckBackups(),
        ];
    }

    /**
     * عدد النسخ العالقة (running/pending تجاوزت المهلة) — نفس منطق
     * backup:reconcile-stuck لكن للعرض فقط (بدون أي تعديل).
     */
    public function countStuckBackups(): int
    {
        $runningTimeout = (int) config('backup.stuck_job_timeout_minutes', 90);
        $pendingTimeout = (int) config('backup.stuck_pending_timeout_minutes', 30);

        $stuckRunning = Backup::where('status', 'running')
            ->where('started_at', '<', now()->subMinutes($runningTimeout))
            ->count();

        $stuckPending = Backup::where('status', 'pending')
            ->where('created_at', '<', now()->subMinutes($pendingTimeout))
            ->count();

        return $stuckRunning + $stuckPending;
    }

    /**
     * استعادة قاعدة البيانات من ملف SQL عبر PDO (بدون تمرير كلمة المرور في الشل).
     */
    private function restoreDatabase(?string $sqlFile): void
    {
        if (! $sqlFile || ! is_file($sqlFile)) {
            throw new \RuntimeException('ملف database.sql غير موجود داخل النسخة.');
        }

        $sql = file_get_contents($sqlFile);
        if ($sql === false || trim($sql) === '') {
            throw new \RuntimeException('ملف قاعدة البيانات فارغ أو غير قابل للقراءة.');
        }

        $statements = $this->splitSqlStatements($sql);
        if ($statements === []) {
            throw new \RuntimeException('لم يتم العثور على أوامر SQL صالحة داخل ملف النسخة.');
        }

        $originalSqlMode = null;
        try {
            $originalSqlMode = DB::selectOne('SELECT @@SESSION.sql_mode as mode')->mode ?? '';
        } catch (\Throwable $e) {
            // نتابع بدون استعادة sql_mode لاحقاً إن تعذّرت قراءته أصلاً.
        }

        // تخفيف sql_mode مؤقتاً أثناء الاستعادة: ملف النسخة قد يحوي جداول/بيانات
        // أُنشئت قبل تفعيل STRICT_TRANS_TABLES/NO_ZERO_DATE في اتصال المشروع
        // (مثل عمود تاريخ NOT NULL بقيمة افتراضية '0000-00-00 00:00:00') — إعادة
        // تطبيقها حرفياً يجب أن تنجح لأن هدف الاستعادة هو إعادة الحالة كما كانت
        // فعلاً، وليس التحقق من توافقها مع قواعد الاتصال الحالية.
        DB::statement("SET SESSION sql_mode = ''");
        DB::statement('SET FOREIGN_KEY_CHECKS=0');

        try {
            foreach ($statements as $statement) {
                try {
                    DB::unprepared($statement);
                } catch (\Throwable $e) {
                    // اتصال MySQL غالباً ما يصبح غير صالح تماماً بعد خطأ كهذا (مثل
                    // "packet bigger than max_allowed_packet" أو انقطاع الاتصال) —
                    // نعيد الاتصال فوراً حتى لا تفشل بصمت كل استعلامات قاعدة
                    // البيانات اللاحقة في هذا الطلب (تسجيل الخطأ، وحتى حفظ جلسة
                    // Laravel نفسها عند نهاية الطلب) بخطأ "gone away" مُضلِّل.
                    DB::reconnect();

                    throw new \RuntimeException(
                        'فشل تنفيذ أحد أوامر SQL أثناء الاستعادة: ' . $e->getMessage(),
                        previous: $e
                    );
                }
            }
        } finally {
            try {
                DB::statement('SET FOREIGN_KEY_CHECKS=1');
                if ($originalSqlMode !== null) {
                    DB::statement('SET SESSION sql_mode = ' . DB::getPdo()->quote($originalSqlMode));
                }
            } catch (\Throwable $e) {
                // الاتصال قد يكون أُعيد إنشاؤه للتو بعد خطأ أعلاه؛ لا داعي لإخفاء
                // الخطأ الأصلي باستثناء ثانوي هنا.
            }
        }
    }

    /**
     * استعادة الملفات إلى storage/app/public (دمج بدون مسح كامل).
     */
    private function restoreFiles(?string $filesDir): void
    {
        if (! $filesDir || ! is_dir($filesDir)) {
            throw new \RuntimeException('مجلد الملفات غير موجود داخل النسخة.');
        }

        $this->copyDirectory($filesDir, storage_path('app/public'));
    }

    /**
     * استعادة الإعدادات إلى مساراتها الصحيحة.
     */
    private function restoreConfig(?string $configDir): void
    {
        if (! $configDir || ! is_dir($configDir)) {
            throw new \RuntimeException('مجلد الإعدادات غير موجود داخل النسخة.');
        }

        $map = [
            '.env' => base_path('.env'),
            'app.php' => config_path('app.php'),
            'database.php' => config_path('database.php'),
            'mail.php' => config_path('mail.php'),
        ];

        $restored = 0;
        $timestamp = now()->format('YmdHis');

        foreach ($map as $name => $destination) {
            $source = $configDir . DIRECTORY_SEPARATOR . $name;
            if (! is_file($source)) {
                continue;
            }

            // نسخة احتياطية بطابع زمني قبل الاستبدال لكل ملف (وليس .env فقط)،
            // حتى يمكن التراجع يدوياً إن احتاج الأمر بعد الاستعادة.
            if (is_file($destination)) {
                copy($destination, $destination . '.pre-restore-' . $timestamp);
            }

            $this->ensureDirectory(dirname($destination));
            if (! copy($source, $destination)) {
                throw new \RuntimeException('تعذر استعادة الملف: ' . $name);
            }

            $restored++;
        }

        if ($restored === 0) {
            throw new \RuntimeException('لم يتم العثور على ملفات إعدادات معروفة داخل النسخة.');
        }
    }

    /**
     * استعادة كاملة من مسارات محلولة مسبقاً، مع لقطة قاعدة بيانات تعويضية:
     * إذا نجحت استعادة قاعدة البيانات ثم فشلت مرحلة لاحقة (ملفات/إعدادات)،
     * نعيد قاعدة البيانات إلى حالتها قبل هذه الاستعادة بدل تركها في حالة
     * وسيطة غير متّسقة مع بقية النظام. لا معاملات DB حقيقية ممكنة هنا لأن
     * dump/restore الخام يستخدم DDL (DROP/CREATE TABLE) الذي يُنهي أي معاملة
     * MySQL ضمنياً، لذا اللقطة التعويضية هي البديل العملي الصحيح.
     *
     * @param  array{database:?string,files:?string,config:?string}  $paths
     */
    private function restoreFull(Backup $backup, array $paths): void
    {
        $preRestoreDumpPath = storage_path('app/backups/temp/pre_restore_' . $backup->id . '_' . uniqid('', true) . '.sql');

        try {
            $this->writeDatabaseDump($backup, $preRestoreDumpPath);
        } catch (\Throwable $e) {
            Log::warning('تعذّر أخذ لقطة قاعدة بيانات قبل الاستعادة — سيتم المتابعة بدون شبكة أمان للتراجع.', [
                'backup_id' => $backup->id,
                'error' => $e->getMessage(),
            ]);
            $preRestoreDumpPath = null;
        }

        try {
            $this->restoreDatabase($paths['database']);
            $this->restoreFiles($paths['files']);
            $this->restoreConfig($paths['config']);
        } catch (\Throwable $e) {
            if ($preRestoreDumpPath && is_file($preRestoreDumpPath)) {
                try {
                    $this->restoreDatabase($preRestoreDumpPath);
                    Log::error('فشلت الاستعادة الكاملة بعد استعادة قاعدة البيانات بنجاح — تم التراجع إلى حالة قاعدة البيانات قبل هذه الاستعادة.', [
                        'backup_id' => $backup->id,
                        'original_error' => $e->getMessage(),
                    ]);
                } catch (\Throwable $rollbackException) {
                    Log::critical('فشلت الاستعادة الكاملة وفشل التراجع التعويضي لقاعدة البيانات أيضاً — يلزم تدخّل يدوي فوري.', [
                        'backup_id' => $backup->id,
                        'original_error' => $e->getMessage(),
                        'rollback_error' => $rollbackException->getMessage(),
                    ]);
                }
            }

            throw $e;
        } finally {
            if ($preRestoreDumpPath) {
                $this->deletePath($preRestoreDumpPath);
            }
        }
    }

    /**
     * تحديد امتداد الأرشيف المحلي المؤقت.
     */
    private function resolveArchiveExtension(Backup $backup): string
    {
        $storagePath = (string) ($backup->storage_path ?? '');
        $basename = strtolower(basename($storagePath));

        if (str_ends_with($basename, '.tar.gz')) {
            return 'tar.gz';
        }

        return match ($backup->compression_type) {
            'gzip' => str_ends_with($basename, '.gz') ? 'gz' : 'tar.gz',
            'tar' => 'tar',
            default => 'zip',
        };
    }

    /**
     * اكتشاف مسارات المحتوى داخل الأرشيف بعد فك الضغط.
     *
     * @return array{database:?string,files:?string,config:?string}
     */
    private function resolveRestoreContentPaths(string $extractedPath, string $backupType): array
    {
        $root = rtrim($extractedPath, DIRECTORY_SEPARATOR);

        $database = $this->firstExistingPath([
            $root . DIRECTORY_SEPARATOR . 'database.sql',
            $root . DIRECTORY_SEPARATOR . 'database' . DIRECTORY_SEPARATOR . 'database.sql',
        ]);

        $files = $this->firstExistingDirectory([
            $root . DIRECTORY_SEPARATOR . 'files',
        ]);

        $config = $this->firstExistingDirectory([
            $root . DIRECTORY_SEPARATOR . 'config',
        ]);

        // نسخ الملفات/الإعدادات فقط قد تكون جذر الاستخراج نفسه.
        if ($backupType === 'files' && ! $files) {
            $files = $root;
        }

        if ($backupType === 'config' && ! $config) {
            $config = $root;
        }

        if ($backupType === 'database' && ! $database) {
            $matches = glob($root . DIRECTORY_SEPARATOR . '*.sql') ?: [];
            $database = $matches[0] ?? null;
        }

        return [
            'database' => $database,
            'files' => $files,
            'config' => $config,
        ];
    }

    private function firstExistingPath(array $candidates): ?string
    {
        foreach ($candidates as $path) {
            if (is_file($path)) {
                return $path;
            }
        }

        return null;
    }

    private function firstExistingDirectory(array $candidates): ?string
    {
        foreach ($candidates as $path) {
            if (is_dir($path)) {
                return $path;
            }
        }

        return null;
    }

    /**
     * تقسيم ملف SQL إلى أوامر مع احترام النصوص المقتبسة.
     *
     * @return list<string>
     */
    private function splitSqlStatements(string $sql): array
    {
        $statements = [];
        $buffer = '';
        $inString = false;
        $stringChar = null;
        $length = strlen($sql);

        for ($i = 0; $i < $length; $i++) {
            $char = $sql[$i];
            $prev = $i > 0 ? $sql[$i - 1] : '';

            if (($char === "'" || $char === '"') && $prev !== '\\') {
                if (! $inString) {
                    $inString = true;
                    $stringChar = $char;
                } elseif ($char === $stringChar) {
                    $inString = false;
                    $stringChar = null;
                }
            }

            if ($char === ';' && ! $inString) {
                $statement = trim($buffer);
                if ($statement !== '' && ! str_starts_with($statement, '--')) {
                    $statements[] = $statement;
                }
                $buffer = '';
                continue;
            }

            $buffer .= $char;
        }

        $tail = trim($buffer);
        if ($tail !== '' && ! str_starts_with($tail, '--')) {
            $statements[] = $tail;
        }

        return $statements;
    }

    /**
     * نسخ مجلد
     */
    private function copyDirectory(string $source, string $dest): void
    {
        if (! is_dir($source)) {
            return;
        }

        $this->ensureDirectory($dest);

        $iterator = new \RecursiveIteratorIterator(
            new \RecursiveDirectoryIterator($source, \RecursiveDirectoryIterator::SKIP_DOTS),
            \RecursiveIteratorIterator::SELF_FIRST
        );

        foreach ($iterator as $item) {
            $destPath = $dest . DIRECTORY_SEPARATOR . $iterator->getSubPathName();
            if ($item->isDir()) {
                $this->ensureDirectory($destPath);
            } else {
                $this->ensureDirectory(dirname($destPath));
                copy($item->getPathname(), $destPath);
            }
        }
    }

    /**
     * تنظيف الملفات المحلية المؤقتة بعد محاولة النسخ.
     * الملف المعتمد يبقى في التخزين العام (storage_path).
     */
    private function cleanupLocalBackupArtifacts(Backup $backup, ?string $sourcePath, ?string $compressedPath): void
    {
        $knownTempPaths = [
            storage_path('app/backups/temp/' . $backup->id),
            storage_path('app/backups/temp/files_' . $backup->id),
            storage_path('app/backups/temp/config_' . $backup->id),
            storage_path('app/backups/temp/database_' . $backup->id),
        ];

        foreach ($knownTempPaths as $path) {
            $this->deletePath($path);
        }

        if ($sourcePath) {
            $this->deletePath($sourcePath);
        }

        $backup->refresh();

        if ($compressedPath && file_exists($compressedPath)) {
            if ($backup->status === 'completed' && $backup->storage_path) {
                @unlink($compressedPath);
                $backup->update(['file_path' => $backup->storage_path]);
            } elseif ($backup->status === 'failed') {
                // أرشيف العمل المحلي لم يُعتمد في التخزين
                @unlink($compressedPath);
            }
        }
    }

    private function ensureDirectory(string $path): void
    {
        if (! is_dir($path)) {
            mkdir($path, 0755, true);
        }
    }

    private function deletePath(?string $path): void
    {
        if (! $path || ! file_exists($path)) {
            return;
        }

        if (is_dir($path)) {
            $iterator = new \RecursiveIteratorIterator(
                new \RecursiveDirectoryIterator($path, \RecursiveDirectoryIterator::SKIP_DOTS),
                \RecursiveIteratorIterator::CHILD_FIRST
            );

            foreach ($iterator as $item) {
                if ($item->isDir()) {
                    @rmdir($item->getPathname());
                } else {
                    @unlink($item->getPathname());
                }
            }

            @rmdir($path);

            return;
        }

        @unlink($path);
    }

    /**
     * إضافة سجل
     */
    private function log(Backup $backup, string $level, string $message, array $context = []): void
    {
        try {
            BackupLog::create([
                'backup_id' => $backup->id,
                'level' => $level,
                'message' => $message,
                'context' => $context,
            ]);
        } catch (\Throwable $e) {
            // إن كان اتصال قاعدة البيانات معطوباً (مثلاً بعد خطأ "gone away" أثناء
            // الاستعادة)، لا نريد أن يُخفي فشل تسجيل الخطأ نفسه الخطأ الأصلي
            // برمي استثناء ثانٍ غير متوقع — التسجيل في ملف السجلات يكفي هنا.
            Log::error('تعذّر تسجيل رسالة في backup_logs: ' . $message, [
                'backup_id' => $backup->id,
                'level' => $level,
                'log_write_error' => $e->getMessage(),
            ]);
        }
    }
}

