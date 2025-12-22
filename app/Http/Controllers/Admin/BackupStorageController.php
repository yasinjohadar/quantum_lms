<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\BackupStorageConfig;
use App\Services\Backup\BackupStorageService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

class BackupStorageController extends Controller
{
    public function __construct(
        private BackupStorageService $storageService
    ) {}

    /**
     * قائمة أماكن التخزين
     */
    public function index()
    {
        $configs = BackupStorageConfig::with('creator')
                                     ->orderBy('priority', 'desc')
                                     ->get();

        return view('admin.pages.backup-storage.index', compact('configs'));
    }

    /**
     * إضافة مكان تخزين
     */
    public function create()
    {
        $drivers = BackupStorageConfig::DRIVERS;
        $config = ['path' => 'backups']; // Default config
        return view('admin.pages.backup-storage.create', compact('drivers', 'config'));
    }

    /**
     * حفظ الإعدادات
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'driver' => 'required|in:' . implode(',', array_keys(BackupStorageConfig::DRIVERS)),
            'config' => 'required|array',
            'priority' => 'integer|min:0',
            'max_backups' => 'nullable|integer|min:1',
        ]);

        try {
            $configData = $request->input('config', []);

            BackupStorageConfig::create([
                'name' => $validated['name'],
                'driver' => $validated['driver'],
                'config' => $configData,
                'priority' => $validated['priority'] ?? 0,
                'max_backups' => $validated['max_backups'] ?? null,
                'created_by' => Auth::id(),
                'is_active' => $request->has('is_active'),
            ]);

            return redirect()->route('admin.backup-storage.index')
                           ->with('success', 'تم إضافة مكان التخزين بنجاح.');
        } catch (\Exception $e) {
            Log::error('Error creating backup storage config: ' . $e->getMessage());
            return redirect()->back()
                           ->with('error', 'حدث خطأ أثناء إضافة مكان التخزين: ' . $e->getMessage())
                           ->withInput();
        }
    }

    /**
     * تعديل الإعدادات
     */
    public function edit(BackupStorageConfig $config)
    {
        $drivers = BackupStorageConfig::DRIVERS;
        $config->load('creator');
        return view('admin.pages.backup-storage.edit', compact('config', 'drivers'));
    }

    /**
     * تحديث الإعدادات
     */
    public function update(Request $request, BackupStorageConfig $config)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'driver' => 'required|in:' . implode(',', array_keys(BackupStorageConfig::DRIVERS)),
            'config' => 'required|array',
            'priority' => 'nullable|integer|min:0',
            'max_backups' => 'nullable|integer|min:1',
        ]);

        try {
            // دمج config مع القيم القديمة (للحفاظ على passwords)
            $configData = $request->input('config', []);
            $oldConfig = $config->getDecryptedConfig();
            
            foreach ($configData as $key => $value) {
                // إذا كان الحقل فارغاً وكان password/token، احتفظ بالقيمة القديمة
                if (empty($value) && (str_contains($key, 'password') || str_contains($key, 'token') || str_contains($key, 'secret') || str_contains($key, 'key'))) {
                    if (isset($oldConfig[$key])) {
                        $configData[$key] = $oldConfig[$key];
                    }
                }
            }

            $config->update([
                'name' => $validated['name'],
                'driver' => $validated['driver'],
                'config' => $configData,
                'priority' => $validated['priority'] ?? 0,
                'max_backups' => $validated['max_backups'] ?? null,
                'is_active' => $request->has('is_active'),
            ]);

            return redirect()->route('admin.backup-storage.index')
                           ->with('success', 'تم تحديث إعدادات التخزين بنجاح.');
        } catch (\Exception $e) {
            Log::error('Error updating backup storage config: ' . $e->getMessage());
            return redirect()->back()
                           ->with('error', 'حدث خطأ أثناء تحديث الإعدادات: ' . $e->getMessage())
                           ->withInput();
        }
    }

    /**
     * حذف الإعدادات
     */
    public function destroy(BackupStorageConfig $config)
    {
        try {
            $config->delete();

            return redirect()->route('admin.backup-storage.index')
                           ->with('success', 'تم حذف إعدادات التخزين بنجاح.');
        } catch (\Exception $e) {
            Log::error('Error deleting backup storage config: ' . $e->getMessage());
            return redirect()->back()
                           ->with('error', 'حدث خطأ أثناء حذف الإعدادات: ' . $e->getMessage());
        }
    }

    /**
     * اختبار الاتصال
     */
    public function test(BackupStorageConfig $config)
    {
        try {
            $result = $config->testConnection();

            return response()->json($result);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'خطأ في الاختبار: ' . $e->getMessage(),
            ], 500);
        }
    }
}
