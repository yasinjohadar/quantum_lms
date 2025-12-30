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
     * إنشاء نسخة احتياطية
     */
    public function createBackup(array $options): Backup
    {
        $backup = Backup::create([
            'name' => $options['name'] ?? 'backup_' . now()->format('Y-m-d_H-i-s'),
            'type' => $options['type'] ?? 'manual',
            'backup_type' => $options['backup_type'] ?? 'full',
            'storage_driver' => $options['storage_driver'] ?? 'local',
            'storage_path' => null, // سيتم تعيينه بعد الرفع
            'file_path' => null, // سيتم تعيينه بعد الإنشاء
            'compression_type' => $options['compression_type'] ?? 'zip',
            'status' => 'pending',
            'retention_days' => $options['retention_days'] ?? 30,
            'created_by' => $options['created_by'] ?? auth()->id(),
            'schedule_id' => $options['schedule_id'] ?? null,
        ]);

        $backup->update([
            'expires_at' => $backup->calculateExpiresAt(),
            'started_at' => now(),
            'status' => 'running',
        ]);

        try {
            $this->log($backup, 'info', 'بدء عملية النسخ الاحتياطي');

            $filePath = match($backup->backup_type) {
                'full' => $this->createFullBackup($backup, $options),
                'database' => $this->createDatabaseBackup($backup, $options),
                'files' => $this->createFilesBackup($backup, $options),
                'config' => $this->createConfigBackup($backup, $options),
                default => throw new \Exception('نوع النسخ غير معروف'),
            };

            // تحديث file_path قبل الضغط
            $backup->update(['file_path' => $filePath]);

            // ضغط الملف
            $compressedPath = $this->compressionService->compress($backup, $backup->compression_type);

            // رفع الملف إلى التخزين مع Auto-failover
            $this->storageManager->storeWithFailover($backup, $compressedPath);
            
            // تخزين في أماكن متعددة إذا كان مفعلاً
            $this->storageManager->storeToMultipleStorages($backup, $compressedPath);
            
            $storagePath = $backup->storage_path;

            $duration = now()->diffInSeconds($backup->started_at);
            
            // الحصول على حجم الملف - استخدام filesize() لأن compressedPath هو مسار كامل
            if (!file_exists($compressedPath)) {
                throw new \Exception('ملف النسخة الاحتياطية غير موجود: ' . $compressedPath);
            }
            
            $fileSize = filesize($compressedPath);
            if ($fileSize === false) {
                throw new \Exception('فشل في الحصول على حجم ملف النسخة الاحتياطية: ' . $compressedPath);
            }

            $backup->update([
                'status' => 'completed',
                'completed_at' => now(),
                'duration' => $duration,
                'file_path' => $compressedPath,
                'storage_path' => $storagePath,
                'file_size' => $fileSize,
            ]);

            $this->log($backup, 'info', 'اكتملت عملية النسخ الاحتياطي بنجاح');
            $this->notificationService->notifyBackupCompleted($backup);

            return $backup->fresh();
        } catch (\Exception $e) {
            $backup->update([
                'status' => 'failed',
                'completed_at' => now(),
                'error_message' => $e->getMessage(),
            ]);

            $this->log($backup, 'error', 'فشلت عملية النسخ الاحتياطي: ' . $e->getMessage());
            $this->notificationService->notifyBackupFailed($backup, $e->getMessage());

            throw $e;
        }
    }

    /**
     * إنشاء نسخة كاملة
     */
    public function createFullBackup(Backup $backup, array $options): string
    {
        $this->log($backup, 'info', 'بدء نسخ قاعدة البيانات');
        $dbPath = $this->createDatabaseBackup($backup, $options);

        $this->log($backup, 'info', 'بدء نسخ الملفات');
        $filesPath = $this->createFilesBackup($backup, $options);

        $this->log($backup, 'info', 'بدء نسخ الإعدادات');
        $configPath = $this->createConfigBackup($backup, $options);

        // دمج جميع الملفات في مجلد واحد
        $backupDir = storage_path('app/backups/temp/' . $backup->id);
        if (!is_dir($backupDir)) {
            mkdir($backupDir, 0755, true);
        }

        copy($dbPath, $backupDir . '/database.sql');
        $this->extractToDirectory($filesPath, $backupDir . '/files');
        $this->extractToDirectory($configPath, $backupDir . '/config');

        return $backupDir;
    }

    /**
     * إنشاء نسخة قاعدة البيانات
     */
    public function createDatabaseBackup(Backup $backup, array $options): string
    {
        $filename = 'database_' . now()->format('Y-m-d_H-i-s') . '.sql';
        $backupDir = storage_path('app/backups');
        $path = $backupDir . '/' . $filename;

        // التأكد من وجود المجلد
        if (!is_dir($backupDir)) {
            mkdir($backupDir, 0755, true);
        }

        $database = config('database.connections.mysql.database');
        $username = config('database.connections.mysql.username');
        $password = config('database.connections.mysql.password');
        $host = config('database.connections.mysql.host');
        $port = config('database.connections.mysql.port', 3306);

        // استخدام Laravel DB facade بدلاً من mysqldump
        try {
            $tables = DB::select('SHOW TABLES');
            $databaseName = $database;
            $tablesKey = 'Tables_in_' . $databaseName;
            
            $sqlContent = "-- Database Backup\n";
            $sqlContent .= "-- Generated: " . now()->toDateTimeString() . "\n";
            $sqlContent .= "-- Database: {$databaseName}\n\n";
            $sqlContent .= "SET FOREIGN_KEY_CHECKS=0;\n\n";

            foreach ($tables as $table) {
                $tableName = $table->$tablesKey;
                
                // الحصول على CREATE TABLE statement
                $createTable = DB::select("SHOW CREATE TABLE `{$tableName}`");
                $sqlContent .= "DROP TABLE IF EXISTS `{$tableName}`;\n";
                $sqlContent .= $createTable[0]->{'Create Table'} . ";\n\n";

                // الحصول على البيانات
                $rows = DB::table($tableName)->get();
                if ($rows->count() > 0) {
                    $sqlContent .= "LOCK TABLES `{$tableName}` WRITE;\n";
                    
                    // الحصول على أسماء الأعمدة من أول صف
                    $firstRow = (array) $rows->first();
                    $columns = array_map(function ($col) {
                        return "`{$col}`";
                    }, array_keys($firstRow));
                    $columnsStr = implode(", ", $columns);
                    
                    $values = [];
                    $chunkSize = 100;
                    $currentChunk = 0;
                    
                    foreach ($rows as $row) {
                        $rowArray = (array) $row;
                        
                        $valArray = array_map(function ($val) {
                            if ($val === null) {
                                return 'NULL';
                            }
                            return DB::getPdo()->quote($val);
                        }, array_values($rowArray));
                        
                        $values[] = "(" . implode(", ", $valArray) . ")";
                        $currentChunk++;
                        
                        // كتابة كل 100 صف
                        if ($currentChunk >= $chunkSize) {
                            $valuesStr = implode(",\n", $values);
                            $sqlContent .= "INSERT INTO `{$tableName}` ({$columnsStr}) VALUES\n{$valuesStr};\n\n";
                            $values = [];
                            $currentChunk = 0;
                        }
                    }
                    
                    // كتابة الصفوف المتبقية
                    if (!empty($values)) {
                        $valuesStr = implode(",\n", $values);
                        $sqlContent .= "INSERT INTO `{$tableName}` ({$columnsStr}) VALUES\n{$valuesStr};\n\n";
                    }
                    
                    $sqlContent .= "UNLOCK TABLES;\n\n";
                }
            }

            $sqlContent .= "SET FOREIGN_KEY_CHECKS=1;\n";

            file_put_contents($path, $sqlContent);

            if (!file_exists($path) || filesize($path) === 0) {
                throw new \Exception('فشل في إنشاء ملف النسخة الاحتياطية - الملف فارغ أو غير موجود');
            }

            $this->log($backup, 'info', 'تم نسخ قاعدة البيانات بنجاح');

            return $path;
        } catch (\Exception $e) {
            Log::error('Database backup failed: ' . $e->getMessage(), [
                'backup_id' => $backup->id,
                'trace' => $e->getTraceAsString(),
            ]);
            throw new \Exception('فشل في نسخ قاعدة البيانات: ' . $e->getMessage());
        }
    }

    /**
     * إنشاء نسخة الملفات
     */
    public function createFilesBackup(Backup $backup, array $options): string
    {
        $filesDir = storage_path('app/public');
        $backupDir = storage_path('app/backups/temp/files_' . $backup->id);

        if (!is_dir($backupDir)) {
            mkdir($backupDir, 0755, true);
        }

        $this->copyDirectory($filesDir, $backupDir);

        $this->log($backup, 'info', 'تم نسخ الملفات بنجاح');

        return $backupDir;
    }

    /**
     * إنشاء نسخة الإعدادات
     */
    public function createConfigBackup(Backup $backup, array $options): string
    {
        $configFiles = [
            '.env',
            'config/app.php',
            'config/database.php',
            'config/mail.php',
        ];

        $backupDir = storage_path('app/backups/temp/config_' . $backup->id);
        if (!is_dir($backupDir)) {
            mkdir($backupDir, 0755, true);
        }

        foreach ($configFiles as $file) {
            $sourcePath = base_path($file);
            if (file_exists($sourcePath)) {
                $destPath = $backupDir . '/' . basename($file);
                copy($sourcePath, $destPath);
            }
        }

        $this->log($backup, 'info', 'تم نسخ الإعدادات بنجاح');

        return $backupDir;
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
     * تحميل نسخة
     */
    public function downloadBackup(Backup $backup): BinaryFileResponse
    {
        $fileContent = $this->storageManager->retrieve($backup);
        $tempFilePath = storage_path('app/temp/download_' . $backup->id . '_' . time() . '.' . $backup->compression_type);
        
        if (!is_dir(dirname($tempFilePath))) {
            mkdir(dirname($tempFilePath), 0755, true);
        }
        
        file_put_contents($tempFilePath, $fileContent);
        $filePath = $tempFilePath;

        if (!file_exists($filePath)) {
            throw new \Exception('الملف غير موجود');
        }

        return response()->download($filePath, $backup->name . '.' . $backup->compression_type);
    }

    /**
     * استعادة نسخة
     */
    public function restoreBackup(Backup $backup, array $options = []): bool
    {
        try {
            $this->log($backup, 'info', 'بدء عملية الاستعادة');

            $fileContent = $this->storageManager->retrieve($backup);
            $tempFilePath = storage_path('app/temp/restore_' . $backup->id . '_' . time() . '.zip');
            
            if (!is_dir(dirname($tempFilePath))) {
                mkdir(dirname($tempFilePath), 0755, true);
            }
            
            file_put_contents($tempFilePath, $fileContent);
            $filePath = $tempFilePath;

            // فك الضغط
            $extractedPath = $this->compressionService->decompress($filePath, storage_path('app/backups/restore_' . $backup->id));

            // استعادة حسب النوع
            match($backup->backup_type) {
                'database' => $this->restoreDatabase($extractedPath),
                'files' => $this->restoreFiles($extractedPath),
                'config' => $this->restoreConfig($extractedPath),
                'full' => $this->restoreFull($extractedPath),
                default => throw new \Exception('نوع النسخ غير معروف'),
            };

            $this->log($backup, 'info', 'اكتملت عملية الاستعادة بنجاح');

            return true;
        } catch (\Exception $e) {
            $this->log($backup, 'error', 'فشلت عملية الاستعادة: ' . $e->getMessage());
            throw $e;
        }
    }

    /**
     * تنظيف النسخ المنتهية الصلاحية
     */
    public function cleanupExpiredBackups(): int
    {
        $expiredBackups = Backup::expired()->get();
        $count = 0;

        foreach ($expiredBackups as $backup) {
            try {
                $this->deleteBackup($backup);
                $count++;
            } catch (\Exception $e) {
                \Log::error('Error deleting expired backup: ' . $e->getMessage());
            }
        }

        return $count;
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
        ];
    }

    /**
     * استعادة قاعدة البيانات
     */
    private function restoreDatabase(string $filePath): void
    {
        $database = config('database.connections.mysql.database');
        $username = config('database.connections.mysql.username');
        $password = config('database.connections.mysql.password');
        $host = config('database.connections.mysql.host');

        $command = sprintf(
            'mysql --user=%s --password=%s --host=%s %s < %s',
            escapeshellarg($username),
            escapeshellarg($password),
            escapeshellarg($host),
            escapeshellarg($database),
            escapeshellarg($filePath)
        );

        exec($command, $output, $returnVar);

        if ($returnVar !== 0) {
            throw new \Exception('فشل في استعادة قاعدة البيانات');
        }
    }

    /**
     * استعادة الملفات
     */
    private function restoreFiles(string $filePath): void
    {
        $destDir = storage_path('app/public');
        $this->copyDirectory($filePath, $destDir);
    }

    /**
     * استعادة الإعدادات
     */
    private function restoreConfig(string $filePath): void
    {
        $files = glob($filePath . '/*');
        foreach ($files as $file) {
            $destPath = base_path('config/' . basename($file));
            copy($file, $destPath);
        }
    }

    /**
     * استعادة كاملة
     */
    private function restoreFull(string $filePath): void
    {
        $this->restoreDatabase($filePath . '/database.sql');
        $this->restoreFiles($filePath . '/files');
        $this->restoreConfig($filePath . '/config');
    }

    /**
     * نسخ مجلد
     */
    private function copyDirectory(string $source, string $dest): void
    {
        if (!is_dir($dest)) {
            mkdir($dest, 0755, true);
        }

        $iterator = new \RecursiveIteratorIterator(
            new \RecursiveDirectoryIterator($source, \RecursiveDirectoryIterator::SKIP_DOTS),
            \RecursiveIteratorIterator::SELF_FIRST
        );

        foreach ($iterator as $item) {
            $destPath = $dest . DIRECTORY_SEPARATOR . $iterator->getSubPathName();
            if ($item->isDir()) {
                if (!is_dir($destPath)) {
                    mkdir($destPath, 0755, true);
                }
            } else {
                copy($item, $destPath);
            }
        }
    }

    /**
     * استخراج إلى مجلد
     */
    private function extractToDirectory(string $archivePath, string $destDir): void
    {
        if (!is_dir($destDir)) {
            mkdir($destDir, 0755, true);
        }

        $zip = new \ZipArchive();
        if ($zip->open($archivePath) === true) {
            $zip->extractTo($destDir);
            $zip->close();
        }
    }

    /**
     * إضافة سجل
     */
    private function log(Backup $backup, string $level, string $message, array $context = []): void
    {
        BackupLog::create([
            'backup_id' => $backup->id,
            'level' => $level,
            'message' => $message,
            'context' => $context,
        ]);
    }
}

