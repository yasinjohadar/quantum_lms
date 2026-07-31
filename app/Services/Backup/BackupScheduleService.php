<?php

namespace App\Services\Backup;

use App\Models\Backup;
use App\Models\BackupSchedule;
use Carbon\Carbon;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;

class BackupScheduleService
{
    private int $scheduleLockSeconds;

    public function __construct(
        private BackupService $backupService
    ) {
        $this->scheduleLockSeconds = (int) config('backup.schedule_lock_seconds', 3600);
    }

    /**
     * إنشاء جدولة
     */
    public function createSchedule(array $data): BackupSchedule
    {
        $schedule = BackupSchedule::create($data);
        $schedule->update(['next_run_at' => $schedule->calculateNextRun()]);

        return $schedule->fresh();
    }

    /**
     * تحديث جدولة
     */
    public function updateSchedule(BackupSchedule $schedule, array $data): BackupSchedule
    {
        $schedule->update($data);
        $schedule->update(['next_run_at' => $schedule->fresh()->calculateNextRun()]);

        return $schedule->fresh();
    }

    /**
     * حذف جدولة
     */
    public function deleteSchedule(BackupSchedule $schedule): bool
    {
        return (bool) $schedule->delete();
    }

    /**
     * تنفيذ جدولة (يدوياً أو من المجدول).
     * يحدّث next_run_at دائماً حتى عند الفشل لتفادي إعادة المحاولة كل دقيقة.
     */
    public function executeSchedule(BackupSchedule $schedule): ?Backup
    {
        $lock = Cache::lock($this->scheduleLockKey($schedule), $this->scheduleLockSeconds);

        if (! $lock->get()) {
            throw new \RuntimeException('هذه الجدولة قيد التنفيذ حالياً. حاول لاحقاً.');
        }

        $backup = null;

        try {
            $storageTargets = $schedule->storage_drivers ?? [];
            $compressionTypes = $schedule->compression_types ?? ['zip'];
            $backups = collect();

            if (empty($storageTargets)) {
                $fallbackId = \App\Models\AppStorageConfig::where('is_active', true)
                    ->orderBy('priority', 'desc')
                    ->value('id');
                $storageTargets = $fallbackId ? [$fallbackId] : [];
            }

            if (empty($storageTargets)) {
                throw new \RuntimeException('لا توجد أماكن تخزين عامة نشطة لتشغيل هذه الجدولة.');
            }

            if (empty($compressionTypes)) {
                $compressionTypes = ['zip'];
            }

            foreach ($storageTargets as $target) {
                foreach ($compressionTypes as $compression) {
                    $options = [
                        'name' => $schedule->name . '_' . now()->format('Y-m-d_H-i-s'),
                        'type' => 'scheduled',
                        'backup_type' => $schedule->backup_type,
                        'compression_type' => $compression,
                        'retention_days' => $schedule->retention_days,
                        'backup_schedule_id' => $schedule->id,
                        'storage_config_id' => (int) $target,
                    ];

                    $backup = $this->backupService->queueBackup($options);
                    $backups->push($backup);
                }
            }

            return $backups->first();
        } finally {
            $schedule->refresh();
            $schedule->update([
                'last_run_at' => now(),
                'next_run_at' => $schedule->calculateNextRun(),
            ]);

            optional($lock)->release();
        }
    }

    /**
     * تشغيل النسخ المجدولة المستحقة مع قفل عام وقفل لكل جدولة.
     */
    public function runScheduledBackups(): int
    {
        $globalLock = Cache::lock('backup-run-scheduled-global', $this->scheduleLockSeconds);

        if (! $globalLock->get()) {
            Log::info('backup:run-scheduled skipped because another run is still active');

            return 0;
        }

        $count = 0;

        try {
            $schedules = BackupSchedule::query()
                ->where('is_active', true)
                ->whereNotNull('next_run_at')
                ->where('next_run_at', '<=', now())
                ->orderBy('next_run_at')
                ->get();

            foreach ($schedules as $schedule) {
                if (! $schedule->shouldRun()) {
                    continue;
                }

                try {
                    $this->executeSchedule($schedule);
                    $count++;
                } catch (\Throwable $e) {
                    Log::error('Error executing backup schedule: ' . $e->getMessage(), [
                        'backup_schedule_id' => $schedule->id,
                    ]);
                }
            }
        } finally {
            optional($globalLock)->release();
        }

        return $count;
    }

    /**
     * حساب وقت التشغيل التالي
     */
    public function calculateNextRun(BackupSchedule $schedule, ?Carbon $from = null): Carbon
    {
        return $schedule->calculateNextRun($from);
    }

    /**
     * التحقق من وجوب التشغيل
     */
    public function shouldRun(BackupSchedule $schedule, ?Carbon $at = null): bool
    {
        return $schedule->shouldRun($at);
    }

    private function scheduleLockKey(BackupSchedule $schedule): string
    {
        return 'backup-schedule-execute-' . $schedule->id;
    }

    /**
     * عدد الجدولات النشطة "المتأخرة" — next_run_at أقدم من الآن بأكثر من مهلة
     * سماح، دليل على أن queue:work أو schedule:run/work لا يعملان على الخادم.
     */
    public function countOverdueSchedules(int $graceMinutes = 10): int
    {
        return BackupSchedule::where('is_active', true)
            ->whereNotNull('next_run_at')
            ->where('next_run_at', '<', now()->subMinutes($graceMinutes))
            ->count();
    }
}
