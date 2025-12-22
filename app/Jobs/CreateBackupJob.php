<?php

namespace App\Jobs;

use App\Models\Backup;
use App\Services\Backup\BackupService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

class CreateBackupJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public function __construct(
        public Backup $backup,
        public array $options = []
    ) {}

    /**
     * Execute the job.
     */
    public function handle(BackupService $backupService): void
    {
        try {
            $backupService->createBackup(array_merge($this->options, [
                'backup_id' => $this->backup->id,
            ]));
        } catch (\Exception $e) {
            Log::error('Error creating backup in job: ' . $e->getMessage(), [
                'backup_id' => $this->backup->id,
            ]);
            
            $this->backup->update([
                'status' => 'failed',
                'error_message' => $e->getMessage(),
            ]);
        }
    }
}
