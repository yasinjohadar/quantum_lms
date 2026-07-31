<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

return new class extends Migration
{
    /**
     * تحويل أي قيمة نصية قديمة (اسم driver) داخل backup_schedules.storage_drivers
     * إلى معرّف AppStorageConfig الرقمي المطابق، تمهيداً لإزالة الفرع is_numeric()
     * وقت التشغيل في BackupScheduleService::executeSchedule().
     *
     * أي جدولة يتعذّر حلّ كل عناصرها تُعطَّل (is_active=false) بدل حذفها بصمت،
     * حتى يراجعها مدير النظام يدوياً بدل أن تتوقف عن العمل دون تفسير.
     */
    public function up(): void
    {
        $schedules = DB::table('backup_schedules')->get(['id', 'storage_drivers', 'is_active']);

        foreach ($schedules as $schedule) {
            $targets = json_decode((string) $schedule->storage_drivers, true);
            if (! is_array($targets) || $targets === []) {
                continue;
            }

            $resolved = [];
            $unresolved = [];

            foreach ($targets as $target) {
                if (is_numeric($target)) {
                    $resolved[] = (int) $target;
                    continue;
                }

                $configId = DB::table('app_storage_configs')
                    ->where('driver', (string) $target)
                    ->where('is_active', true)
                    ->orderByDesc('priority')
                    ->value('id');

                if ($configId) {
                    $resolved[] = (int) $configId;
                } else {
                    $unresolved[] = $target;
                }
            }

            $resolved = array_values(array_unique($resolved));

            $update = ['storage_drivers' => json_encode($resolved)];

            if ($resolved === []) {
                $update['is_active'] = false;
                Log::warning('Backup schedule disabled during storage_drivers backfill — none of its legacy driver names matched an active AppStorageConfig.', [
                    'backup_schedule_id' => $schedule->id,
                    'unresolved_targets' => $unresolved,
                ]);
            } elseif ($unresolved !== []) {
                Log::warning('Backup schedule partially backfilled — some legacy storage_drivers entries could not be resolved and were dropped.', [
                    'backup_schedule_id' => $schedule->id,
                    'unresolved_targets' => $unresolved,
                ]);
            }

            DB::table('backup_schedules')->where('id', $schedule->id)->update($update);
        }
    }

    public function down(): void
    {
        //
    }
};
