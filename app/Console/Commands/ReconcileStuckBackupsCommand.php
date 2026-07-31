<?php

namespace App\Console\Commands;

use App\Models\Backup;
use App\Models\BackupLog;
use App\Services\Backup\BackupNotificationService;
use Illuminate\Console\Command;

class ReconcileStuckBackupsCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'backup:reconcile-stuck';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'كشف النسخ العالقة (running/pending منذ وقت طويل بلا تقدّم) وتحويلها إلى failed';

    /**
     * Execute the console command.
     */
    public function handle(BackupNotificationService $notificationService): int
    {
        $runningTimeout = (int) config('backup.stuck_job_timeout_minutes', 90);
        $pendingTimeout = (int) config('backup.stuck_pending_timeout_minutes', 30);

        $stuckRunning = Backup::where('status', 'running')
            ->where('started_at', '<', now()->subMinutes($runningTimeout))
            ->get();

        $stuckPending = Backup::where('status', 'pending')
            ->where('created_at', '<', now()->subMinutes($pendingTimeout))
            ->get();

        $stuck = $stuckRunning->merge($stuckPending);

        if ($stuck->isEmpty()) {
            $this->info('لا توجد نسخ عالقة.');

            return Command::SUCCESS;
        }

        foreach ($stuck as $backup) {
            $reason = $backup->status === 'running'
                ? "تم تحويلها إلى failed تلقائياً: تجاوزت {$runningTimeout} دقيقة في حالة قيد التنفيذ بلا اكتمال — على الأرجح توقف queue worker أو انهار منتصف التنفيذ."
                : "تم تحويلها إلى failed تلقائياً: تجاوزت {$pendingTimeout} دقيقة في حالة معلّق دون أن يلتقطها الطابور — تحقق من تشغيل queue:work.";

            $backup->update([
                'status' => 'failed',
                'completed_at' => now(),
                'error_message' => $reason,
            ]);

            BackupLog::create([
                'backup_id' => $backup->id,
                'level' => 'error',
                'message' => $reason,
            ]);

            try {
                $notificationService->notifyBackupFailed($backup->fresh(), $reason);
            } catch (\Throwable $e) {
                // لا نسمح لفشل الإشعار بإيقاف توفيق باقي النسخ العالقة —
                // تحويل الحالة إلى failed أعلاه هو الهدف الأساسي وقد تم فعلاً.
                \Illuminate\Support\Facades\Log::warning('Failed to send stuck-backup notification', [
                    'backup_id' => $backup->id,
                    'error' => $e->getMessage(),
                ]);
            }

            $this->warn("نسخة #{$backup->id} ({$backup->name}) حُوّلت إلى failed — {$reason}");
        }

        $this->info('تم التوفيق على ' . $stuck->count() . ' نسخة عالقة.');

        return Command::SUCCESS;
    }
}
