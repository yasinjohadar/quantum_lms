<?php

namespace App\Jobs;

use App\Models\Backup;
use App\Services\Backup\BackupService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

class CreateBackupJob implements ShouldQueue, ShouldBeUnique
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * مهلة كافية للنسخ الكبيرة — تُقرأ من config('backup.job_timeout') في المُنشئ.
     */
    public int $timeout = 3600;

    /**
     * لا نعيد المحاولة تلقائياً — processBackup() ليست آمنة لإعادة تشغيل تلقائية
     * مؤكَّدة (قد تكون كتابة جزئية قيد التقدم). شبكة الأمان الفعلية لمهمة تعطّلت
     * منتصف التنفيذ هي أمر backup:reconcile-stuck، وليس إعادة محاولة Laravel.
     */
    public int $tries = 1;

    /**
     * مدة اعتبار المهمة فريدة (ثوانٍ) — تُقرأ من نفس الإعداد.
     */
    public int $uniqueFor = 3600;

    public function __construct(
        public Backup $backup,
        public array $options = []
    ) {
        $this->timeout = (int) config('backup.job_timeout', 3600);
        $this->uniqueFor = $this->timeout;
    }

    public function uniqueId(): string
    {
        return 'create-backup-' . $this->backup->id;
    }

    public function handle(BackupService $backupService): void
    {
        $backupService->processBackup($this->backup, $this->options);
    }

    public function failed(?\Throwable $exception): void
    {
        $message = $exception?->getMessage() ?? 'فشلت مهمة إنشاء النسخة بدون تفاصيل';

        Log::error('CreateBackupJob failed', [
            'backup_id' => $this->backup->id,
            'error' => $message,
        ]);

        $this->backup->refresh();

        if (! in_array($this->backup->status, ['completed', 'failed'], true)) {
            $this->backup->update([
                'status' => 'failed',
                'completed_at' => now(),
                'error_message' => $message,
            ]);
        }
    }
}
