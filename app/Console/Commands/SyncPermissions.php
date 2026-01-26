<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Services\PermissionDiscoveryService;
use Spatie\Permission\Models\Permission;

class SyncPermissions extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'permissions:sync';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'مزامنة الصلاحيات تلقائياً من Controllers';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('بدء مزامنة الصلاحيات...');

        try {
            $discoveryService = app(PermissionDiscoveryService::class);
            $discoveredPermissions = $discoveryService->discoverFromControllers();

            $this->info('تم اكتشاف ' . count($discoveredPermissions) . ' صلاحية.');

            $added = 0;
            $updated = 0;

            foreach ($discoveredPermissions as $permission) {
                $existing = Permission::where('name', $permission['name'])
                    ->where('guard_name', 'web')
                    ->first();

                if ($existing) {
                    // تحديث الوصف إذا تغير
                    if ($existing->description !== $permission['description']) {
                        $existing->update(['description' => $permission['description']]);
                        $updated++;
                    }
                } else {
                    // إضافة صلاحية جديدة
                    Permission::create([
                        'name' => $permission['name'],
                        'guard_name' => 'web',
                        'description' => $permission['description'] ?? null,
                    ]);
                    $added++;
                }
            }

            $this->info("تمت المزامنة بنجاح!");
            $this->info("صلاحيات جديدة: {$added}");
            $this->info("صلاحيات محدثة: {$updated}");

            return Command::SUCCESS;
        } catch (\Exception $e) {
            $this->error('حدث خطأ أثناء مزامنة الصلاحيات: ' . $e->getMessage());
            return Command::FAILURE;
        }
    }
}
