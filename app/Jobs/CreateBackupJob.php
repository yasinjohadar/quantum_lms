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
     * مهلة كافية للنسخ الكبيرة (ساعة).
     */
    public int $timeout = 3600;

    /**
     * لا نعيد المحاولة تلقائياً لتفادي تكرار العمل الثقيل.
     */
    public int $tries = 1;

    /**
     * مدة اعتبار المهمة فريدة (ثوانٍ).
     */
    public int $uniqueFor = 3600;

    public function __construct(
        public Backup $backup,
        public array $options = []
    ) {}

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
