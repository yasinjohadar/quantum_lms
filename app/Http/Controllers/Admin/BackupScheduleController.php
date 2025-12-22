<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\BackupSchedule;
use App\Services\Backup\BackupScheduleService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

class BackupScheduleController extends Controller
{
    public function __construct(
        private BackupScheduleService $scheduleService
    ) {}

    /**
     * قائمة الجدولات
     */
    public function index()
    {
        $schedules = BackupSchedule::with(['creator', 'backups'])
                                  ->latest()
                                  ->paginate(20);

        return view('admin.pages.backup-schedules.index', compact('schedules'));
    }

    /**
     * إنشاء جدولة
     */
    public function create()
    {
        $backupTypes = BackupSchedule::BACKUP_TYPES;
        $frequencies = BackupSchedule::FREQUENCIES;
        $storageDrivers = \App\Models\BackupStorageConfig::where('is_active', true)->pluck('driver', 'id');
        $compressionTypes = \App\Models\Backup::COMPRESSION_TYPES;

        return view('admin.pages.backup-schedules.create', compact(
            'backupTypes',
            'frequencies',
            'storageDrivers',
            'compressionTypes'
        ));
    }

    /**
     * حفظ الجدولة
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'backup_type' => 'required|in:' . implode(',', array_keys(BackupSchedule::BACKUP_TYPES)),
            'frequency' => 'required|in:' . implode(',', array_keys(BackupSchedule::FREQUENCIES)),
            'time' => 'required|date_format:H:i',
            'days_of_week' => 'nullable|array',
            'days_of_week.*' => 'integer|min:0|max:6',
            'day_of_month' => 'nullable|integer|min:1|max:31',
            'storage_drivers' => 'required|array|min:1',
            'storage_drivers.*' => 'string',
            'compression_types' => 'required|array|min:1',
            'compression_types.*' => 'in:' . implode(',', array_keys(\App\Models\Backup::COMPRESSION_TYPES)),
            'retention_days' => 'required|integer|min:1|max:365',
        ]);

        try {
            $schedule = $this->scheduleService->createSchedule(array_merge($validated, [
                'created_by' => Auth::id(),
            ]));

            return redirect()->route('admin.backup-schedules.index')
                           ->with('success', 'تم إنشاء الجدولة بنجاح.');
        } catch (\Exception $e) {
            Log::error('Error creating backup schedule: ' . $e->getMessage());
            return redirect()->back()
                           ->with('error', 'حدث خطأ أثناء إنشاء الجدولة: ' . $e->getMessage())
                           ->withInput();
        }
    }

    /**
     * تعديل جدولة
     */
    public function edit(BackupSchedule $schedule)
    {
        $backupTypes = BackupSchedule::BACKUP_TYPES;
        $frequencies = BackupSchedule::FREQUENCIES;
        $storageConfigs = \App\Models\BackupStorageConfig::where('is_active', true)->get();
        $storageDrivers = $storageConfigs->pluck('driver')->unique()->toArray();
        $compressionTypes = \App\Models\Backup::COMPRESSION_TYPES;

        return view('admin.pages.backup-schedules.edit', compact(
            'schedule',
            'backupTypes',
            'frequencies',
            'storageDrivers',
            'compressionTypes'
        ));
    }

    /**
     * تحديث الجدولة
     */
    public function update(Request $request, BackupSchedule $schedule)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'backup_type' => 'required|in:' . implode(',', array_keys(BackupSchedule::BACKUP_TYPES)),
            'frequency' => 'required|in:' . implode(',', array_keys(BackupSchedule::FREQUENCIES)),
            'time' => 'required|date_format:H:i',
            'days_of_week' => 'nullable|array',
            'days_of_week.*' => 'integer|min:0|max:6',
            'day_of_month' => 'nullable|integer|min:1|max:31',
            'storage_drivers' => 'required|array|min:1',
            'storage_drivers.*' => 'string',
            'compression_types' => 'required|array|min:1',
            'compression_types.*' => 'in:' . implode(',', array_keys(\App\Models\Backup::COMPRESSION_TYPES)),
            'retention_days' => 'required|integer|min:1|max:365',
        ]);

        try {
            $this->scheduleService->updateSchedule($schedule, $validated);

            return redirect()->route('admin.backup-schedules.index')
                           ->with('success', 'تم تحديث الجدولة بنجاح.');
        } catch (\Exception $e) {
            Log::error('Error updating backup schedule: ' . $e->getMessage());
            return redirect()->back()
                           ->with('error', 'حدث خطأ أثناء تحديث الجدولة: ' . $e->getMessage())
                           ->withInput();
        }
    }

    /**
     * حذف الجدولة
     */
    public function destroy(BackupSchedule $schedule)
    {
        try {
            $this->scheduleService->deleteSchedule($schedule);

            return redirect()->route('admin.backup-schedules.index')
                           ->with('success', 'تم حذف الجدولة بنجاح.');
        } catch (\Exception $e) {
            Log::error('Error deleting backup schedule: ' . $e->getMessage());
            return redirect()->back()
                           ->with('error', 'حدث خطأ أثناء حذف الجدولة: ' . $e->getMessage());
        }
    }

    /**
     * تشغيل جدولة يدوياً
     */
    public function execute(BackupSchedule $schedule)
    {
        try {
            $backup = $this->scheduleService->executeSchedule($schedule);

            return redirect()->route('admin.backups.show', $backup)
                           ->with('success', 'تم تشغيل الجدولة بنجاح.');
        } catch (\Exception $e) {
            Log::error('Error executing backup schedule: ' . $e->getMessage());
            return redirect()->back()
                           ->with('error', 'حدث خطأ أثناء تشغيل الجدولة: ' . $e->getMessage());
        }
    }

    /**
     * تفعيل/إلغاء تفعيل
     */
    public function toggleActive(BackupSchedule $schedule)
    {
        try {
            $schedule->update(['is_active' => !$schedule->is_active]);

            return redirect()->back()
                           ->with('success', 'تم تحديث حالة الجدولة بنجاح.');
        } catch (\Exception $e) {
            return redirect()->back()
                           ->with('error', 'حدث خطأ: ' . $e->getMessage());
        }
    }
}
