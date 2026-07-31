<?php

namespace App\Services\Backup;

use App\Models\Backup;
use ZipArchive;

class BackupCompressionService
{
    /**
     * ملاحظة أمنية: لا تشفير على مستوى الملف هنا — الأرشيف الناتج (zip/gzip/tar)
     * يُخزَّن كما هو، وقد يتضمن (لنوع full/config) ملف .env الحقيقي بأسراره.
     * التحسين الموصى به لاحقاً: تشفير AES-256 لاحق للضغط بمفتاح BACKUP_ENCRYPTION_KEY
     * مخصص منفصل عن APP_KEY (راجع config/backup.php وdocs/guides/backup-operations.md).
     * مؤجَّل حالياً بقرار صريح.
     */

    /**
     * ضغط النسخة
     */
    public function compress(Backup $backup, string $type = 'zip'): string
    {
        $source = $backup->file_path;
        if (! $source || ! file_exists($source)) {
            throw new \Exception('مسار الملف غير موجود');
        }

        $this->ensureBackupDirectory();

        return match ($type) {
            'zip' => $this->compressZip($source, $backup->id),
            'gzip' => $this->compressGzip($source, $backup->id),
            'tar' => $this->compressTar($source, $backup->id),
            default => throw new \Exception('نوع الضغط غير معروف'),
        };
    }

    /**
     * ضغط ZIP
     */
    public function compressZip(string $source, int $backupId): string
    {
        $destination = storage_path('app/backups/backup_' . $backupId . '.zip');
        $this->deleteIfExists($destination);

        $zip = new ZipArchive();
        if ($zip->open($destination, ZipArchive::CREATE | ZipArchive::OVERWRITE) !== true) {
            throw new \Exception('فشل في إنشاء ملف ZIP');
        }

        if (is_dir($source)) {
            $this->addDirectoryToZip($source, $zip, '');
        } else {
            $zip->addFile($source, basename($source));
        }

        $zip->close();

        if (! file_exists($destination) || filesize($destination) === 0) {
            throw new \Exception('فشل في إنشاء ملف ZIP صالح');
        }

        return $destination;
    }

    /**
     * ضغط GZIP
     * - ملف واحد: gzip مباشر
     * - مجلد: tar ثم gzip => backup_{id}.tar.gz
     */
    public function compressGzip(string $source, int $backupId): string
    {
        $removeTarAfter = false;
        $tarPath = null;

        if (is_dir($source)) {
            $tarPath = $this->compressTar($source, $backupId, 'temp_backup_' . $backupId . '.tar');
            $source = $tarPath;
            $removeTarAfter = true;
            $destination = storage_path('app/backups/backup_' . $backupId . '.tar.gz');
        } else {
            $destination = storage_path('app/backups/backup_' . $backupId . '.gz');
        }

        $this->deleteIfExists($destination);

        $fpIn = fopen($source, 'rb');
        $fpOut = gzopen($destination, 'wb9');

        if (! $fpIn || ! $fpOut) {
            if ($fpIn) {
                fclose($fpIn);
            }
            if ($fpOut) {
                gzclose($fpOut);
            }
            throw new \Exception('فشل في إنشاء ملف GZIP');
        }

        while (! feof($fpIn)) {
            $chunk = fread($fpIn, 8192);
            if ($chunk === false) {
                break;
            }
            gzwrite($fpOut, $chunk);
        }

        fclose($fpIn);
        gzclose($fpOut);

        if ($removeTarAfter && $tarPath) {
            $this->deleteIfExists($tarPath);
        }

        if (! file_exists($destination) || filesize($destination) === 0) {
            throw new \Exception('فشل في إنشاء ملف GZIP صالح');
        }

        return $destination;
    }

    /**
     * ضغط TAR
     */
    public function compressTar(string $source, int $backupId, ?string $filename = null): string
    {
        $destination = storage_path('app/backups/' . ($filename ?: ('backup_' . $backupId . '.tar')));
        $this->deleteIfExists($destination);

        // PharData يرفض المسارات ذات الامتداد المزدوج أحياناً؛ نضمن ملفاً نظيفاً.
        try {
            $phar = new \PharData($destination);

            if (is_dir($source)) {
                $phar->buildFromDirectory($source);
            } else {
                $phar->addFile($source, basename($source));
            }

            unset($phar);
        } catch (\Throwable $e) {
            $this->deleteIfExists($destination);
            throw new \Exception('فشل في إنشاء ملف TAR: ' . $e->getMessage());
        }

        if (! file_exists($destination) || filesize($destination) === 0) {
            throw new \Exception('فشل في إنشاء ملف TAR صالح');
        }

        return $destination;
    }

    /**
     * فك الضغط
     */
    public function decompress(string $file, string $destination): string
    {
        $basename = strtolower(basename($file));

        if (str_ends_with($basename, '.tar.gz') || str_ends_with($basename, '.tgz')) {
            return $this->decompressTarGz($file, $destination);
        }

        $extension = strtolower(pathinfo($file, PATHINFO_EXTENSION));

        return match ($extension) {
            'zip' => $this->decompressZip($file, $destination),
            'gz' => $this->decompressGzip($file, $destination),
            'tar' => $this->decompressTar($file, $destination),
            default => throw new \Exception('نوع الضغط غير معروف'),
        };
    }

    /**
     * فك ضغط ZIP
     */
    private function decompressZip(string $file, string $destination): string
    {
        if (! is_dir($destination)) {
            mkdir($destination, 0755, true);
        }

        $zip = new ZipArchive();
        if ($zip->open($file) !== true) {
            throw new \Exception('فشل في فتح ملف ZIP');
        }

        $zip->extractTo($destination);
        $zip->close();

        return $destination;
    }

    /**
     * فك ضغط GZIP لملف واحد إلى مجلد الوجهة.
     */
    private function decompressGzip(string $file, string $destination): string
    {
        if (! is_dir($destination)) {
            mkdir($destination, 0755, true);
        }

        $outFile = rtrim($destination, DIRECTORY_SEPARATOR) . DIRECTORY_SEPARATOR . pathinfo($file, PATHINFO_FILENAME);
        // pathinfo على file.sql.gz يعطي file.sql — مناسب
        if (str_ends_with(strtolower($outFile), '.tar')) {
            // يُعالج عبر decompressTarGz
        }

        $fpIn = gzopen($file, 'rb');
        $fpOut = fopen($outFile, 'wb');

        if (! $fpIn || ! $fpOut) {
            throw new \Exception('فشل في فك ضغط GZIP');
        }

        while (! gzeof($fpIn)) {
            $chunk = gzread($fpIn, 8192);
            if ($chunk === false) {
                break;
            }
            fwrite($fpOut, $chunk);
        }

        gzclose($fpIn);
        fclose($fpOut);

        return $destination;
    }

    /**
     * فك ضغط TAR
     */
    private function decompressTar(string $file, string $destination): string
    {
        if (! is_dir($destination)) {
            mkdir($destination, 0755, true);
        }

        $phar = new \PharData($file);
        $phar->extractTo($destination, null, true);

        return $destination;
    }

    /**
     * فك .tar.gz إلى مجلد.
     */
    private function decompressTarGz(string $file, string $destination): string
    {
        if (! is_dir($destination)) {
            mkdir($destination, 0755, true);
        }

        $tempTar = storage_path('app/backups/temp_restore_' . uniqid('', true) . '.tar');
        $this->deleteIfExists($tempTar);

        $fpIn = gzopen($file, 'rb');
        $fpOut = fopen($tempTar, 'wb');

        if (! $fpIn || ! $fpOut) {
            throw new \Exception('فشل في فك ضغط TAR.GZ');
        }

        while (! gzeof($fpIn)) {
            $chunk = gzread($fpIn, 8192);
            if ($chunk === false) {
                break;
            }
            fwrite($fpOut, $chunk);
        }

        gzclose($fpIn);
        fclose($fpOut);

        try {
            $this->decompressTar($tempTar, $destination);
        } finally {
            $this->deleteIfExists($tempTar);
        }

        return $destination;
    }

    /**
     * الحصول على نسبة الضغط
     */
    public function getCompressionRatio(string $file): float
    {
        return 0.0;
    }

    /**
     * إضافة مجلد إلى ZIP
     */
    private function addDirectoryToZip(string $dir, ZipArchive $zip, string $zipDir): void
    {
        $files = scandir($dir);
        if ($files === false) {
            return;
        }

        foreach ($files as $file) {
            if ($file === '.' || $file === '..') {
                continue;
            }

            $filePath = $dir . DIRECTORY_SEPARATOR . $file;
            $zipPath = $zipDir . ($zipDir ? '/' : '') . $file;

            if (is_dir($filePath)) {
                $zip->addEmptyDir($zipPath);
                $this->addDirectoryToZip($filePath, $zip, $zipPath);
            } else {
                $zip->addFile($filePath, $zipPath);
            }
        }
    }

    private function ensureBackupDirectory(): void
    {
        $dir = storage_path('app/backups');
        if (! is_dir($dir)) {
            mkdir($dir, 0755, true);
        }
    }

    private function deleteIfExists(string $path): void
    {
        if (file_exists($path)) {
            @unlink($path);
        }
    }
}
