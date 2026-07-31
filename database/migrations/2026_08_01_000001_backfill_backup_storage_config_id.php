<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

return new class extends Migration
{
    /**
     * ملء backups.storage_config_id من storage_driver القديم لأي صف ناقص،
     * تمهيداً لحذف عمود storage_driver في هجرة لاحقة (dual-path cleanup).
     */
    public function up(): void
    {
        $rows = DB::table('backups')
            ->whereNull('storage_config_id')
            ->whereNotNull('storage_driver')
            ->get(['id', 'storage_driver']);

        foreach ($rows as $row) {
            $configId = DB::table('app_storage_configs')
                ->where('driver', $row->storage_driver)
                ->where('is_active', true)
                ->orderByDesc('priority')
                ->value('id');

            if ($configId) {
                DB::table('backups')->where('id', $row->id)->update([
                    'storage_config_id' => $configId,
                ]);
            } else {
                Log::warning('Backup row could not be backfilled to an AppStorageConfig — no active config matches its legacy storage_driver.', [
                    'backup_id' => $row->id,
                    'storage_driver' => $row->storage_driver,
                ]);
            }
        }
    }

    /**
     * لا تراجع فعلياً — هذه هجرة تعبئة بيانات فقط، والقيم الأصلية (storage_driver)
     * تبقى كما هي دون تعديل، لذا لا يوجد شيء يُعاد.
     */
    public function down(): void
    {
        //
    }
};
