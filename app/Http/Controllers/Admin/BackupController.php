<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Backup;
use App\Services\Backup\BackupService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

class BackupController extends Controller
{
    public function __construct(
        private BackupService $backupService
    ) {}

    /**
     * قائمة النسخ
     */
    public function index(Request $request)
    {
        $query = Backup::with(['creator', 'schedule']);

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        if ($request->filled('backup_type')) {
            $query->where('backup_type', $request->backup_type);
        }

        if ($request->filled('storage_driver')) {
            $query->where('storage_driver', $request->storage_driver);
        }

        $backups = $query->latest()->paginate(20);
        $stats = $this->backupService->getBackupStats();

        return view('admin.pages.backups.index', compact('backups', 'stats'));
    }

    /**
     * إنشاء نسخة يدوية
     */
    public function create()
    {
        $backupTypes = Backup::BACKUP_TYPES;
        $compressionTypes = Backup::COMPRESSION_TYPES;
        $storageConfigs = \App\Models\BackupStorageConfig::where('is_active', true)->get();

        return view('admin.pages.backups.create', compact('backupTypes', 'compressionTypes', 'storageDrivers'));
    }

    /**
     * حفظ النسخة
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'backup_type' => 'required|in:' . implode(',', array_keys(Backup::BACKUP_TYPES)),
            'storage_driver' => 'required|string',
            'compression_type' => 'required|in:' . implode(',', array_keys(Backup::COMPRESSION_TYPES)),
            'retention_days' => 'required|integer|min:1|max:365',
        ]);

        try {
            $backup = $this->backupService->createBackup([
                'name' => $validated['name'],
                'type' => 'manual',
                'backup_type' => $validated['backup_type'],
                'storage_driver' => $validated['storage_driver'],
                'compression_type' => $validated['compression_type'],
                'retention_days' => $validated['retention_days'],
                'created_by' => Auth::id(),
            ]);

            return redirect()->route('admin.backups.show', $backup)
                           ->with('success', 'تم إنشاء النسخة الاحتياطية بنجاح.');
        } catch (\Exception $e) {
            Log::error('Error creating backup: ' . $e->getMessage());
            return redirect()->back()
                           ->with('error', 'حدث خطأ أثناء إنشاء النسخة: ' . $e->getMessage())
                           ->withInput();
        }
    }

    /**
     * عرض تفاصيل النسخة
     */
    public function show(Backup $backup)
    {
        $backup->load(['creator', 'schedule', 'logs']);
        return view('admin.pages.backups.show', compact('backup'));
    }

    /**
     * تحميل النسخة
     */
    public function download(Backup $backup)
    {
        try {
            return $this->backupService->downloadBackup($backup);
        } catch (\Exception $e) {
            return redirect()->back()
                           ->with('error', 'حدث خطأ أثناء تحميل النسخة: ' . $e->getMessage());
        }
    }

    /**
     * استعادة النسخة
     */
    public function restore(Request $request, Backup $backup)
    {
        $validated = $request->validate([
            'confirm' => 'required|accepted',
        ]);

        try {
            $this->backupService->restoreBackup($backup, $request->all());

            return redirect()->route('admin.backups.index')
                           ->with('success', 'تم استعادة النسخة الاحتياطية بنجاح.');
        } catch (\Exception $e) {
            Log::error('Error restoring backup: ' . $e->getMessage());
            return redirect()->back()
                           ->with('error', 'حدث خطأ أثناء استعادة النسخة: ' . $e->getMessage());
        }
    }

    /**
     * حذف النسخة
     */
    public function destroy(Backup $backup)
    {
        try {
            $this->backupService->deleteBackup($backup);

            return redirect()->route('admin.backups.index')
                           ->with('success', 'تم حذف النسخة الاحتياطية بنجاح.');
        } catch (\Exception $e) {
            Log::error('Error deleting backup: ' . $e->getMessage());
            return redirect()->back()
                           ->with('error', 'حدث خطأ أثناء حذف النسخة: ' . $e->getMessage());
        }
    }

    /**
     * إحصائيات
     */
    public function stats()
    {
        $stats = $this->backupService->getBackupStats();
        return response()->json($stats);
    }
}
