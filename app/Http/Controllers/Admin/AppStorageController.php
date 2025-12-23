<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AppStorageConfig;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

class AppStorageController extends Controller
{
    /**
     * قائمة أماكن التخزين
     */
    public function index()
    {
        $configs = AppStorageConfig::with('creator')
                                   ->orderBy('priority', 'desc')
                                   ->get();

        return view('admin.pages.app-storage.index', compact('configs'));
    }

    /**
     * إضافة مكان تخزين
     */
    public function create()
    {
        $drivers = AppStorageConfig::DRIVERS;
        return view('admin.pages.app-storage.create', compact('drivers'));
    }

    /**
     * حفظ الإعدادات
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'driver' => 'required|in:' . implode(',', array_keys(AppStorageConfig::DRIVERS)),
            'config' => 'required|array',
            'priority' => 'integer|min:0',
            'cdn_url' => 'nullable|url',
            'file_types' => 'nullable|array',
        ]);

        try {
            $configData = $request->input('config', []);

            $createData = [
                'name' => $validated['name'],
                'driver' => $validated['driver'],
                'config' => $configData,
                'priority' => $validated['priority'] ?? 0,
                'created_by' => Auth::id(),
                'is_active' => $request->has('is_active'),
                'redundancy' => $request->has('redundancy'),
                'cdn_url' => $request->input('cdn_url'),
                'file_types' => $request->input('file_types'),
            ];

            if ($request->has('pricing_config')) {
                $createData['pricing_config'] = [
                    'storage_cost_per_gb' => $request->input('pricing_config.storage_cost_per_gb'),
                    'upload_cost_per_gb' => $request->input('pricing_config.upload_cost_per_gb'),
                    'download_cost_per_gb' => $request->input('pricing_config.download_cost_per_gb'),
                ];
            }

            if ($request->has('monthly_budget')) {
                $createData['monthly_budget'] = $request->input('monthly_budget');
            }

            if ($request->has('cost_alert_threshold')) {
                $createData['cost_alert_threshold'] = $request->input('cost_alert_threshold');
            }

            AppStorageConfig::create($createData);

            return redirect()->route('admin.app-storage.configs.index')
                           ->with('success', 'تم إضافة مكان التخزين بنجاح.');
        } catch (\Exception $e) {
            Log::error('Error creating app storage config: ' . $e->getMessage());
            return redirect()->back()
                           ->with('error', 'حدث خطأ أثناء إضافة مكان التخزين: ' . $e->getMessage())
                           ->withInput();
        }
    }

    /**
     * تعديل الإعدادات
     */
    public function edit(AppStorageConfig $config)
    {
        $drivers = AppStorageConfig::DRIVERS;
        $config->load('creator');
        return view('admin.pages.app-storage.edit', compact('config', 'drivers'));
    }

    /**
     * تحديث الإعدادات
     */
    public function update(Request $request, AppStorageConfig $config)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'driver' => 'required|in:' . implode(',', array_keys(AppStorageConfig::DRIVERS)),
            'config' => 'required|array',
            'priority' => 'nullable|integer|min:0',
            'cdn_url' => 'nullable|url',
            'file_types' => 'nullable|array',
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

            $updateData = [
                'name' => $validated['name'],
                'driver' => $validated['driver'],
                'config' => $configData,
                'priority' => $validated['priority'] ?? 0,
                'is_active' => $request->has('is_active'),
                'redundancy' => $request->has('redundancy'),
                'cdn_url' => $request->input('cdn_url'),
                'file_types' => $request->input('file_types'),
            ];

            if ($request->has('pricing_config')) {
                $updateData['pricing_config'] = [
                    'storage_cost_per_gb' => $request->input('pricing_config.storage_cost_per_gb'),
                    'upload_cost_per_gb' => $request->input('pricing_config.upload_cost_per_gb'),
                    'download_cost_per_gb' => $request->input('pricing_config.download_cost_per_gb'),
                ];
            }

            if ($request->has('monthly_budget')) {
                $updateData['monthly_budget'] = $request->input('monthly_budget');
            }

            if ($request->has('cost_alert_threshold')) {
                $updateData['cost_alert_threshold'] = $request->input('cost_alert_threshold');
            }

            $config->update($updateData);

            return redirect()->route('admin.app-storage.configs.index')
                           ->with('success', 'تم تحديث إعدادات التخزين بنجاح.');
        } catch (\Exception $e) {
            Log::error('Error updating app storage config: ' . $e->getMessage());
            return redirect()->back()
                           ->with('error', 'حدث خطأ أثناء تحديث الإعدادات: ' . $e->getMessage())
                           ->withInput();
        }
    }

    /**
     * حذف الإعدادات
     */
    public function destroy(AppStorageConfig $config)
    {
        try {
            $config->delete();

            return redirect()->route('admin.app-storage.configs.index')
                           ->with('success', 'تم حذف إعدادات التخزين بنجاح.');
        } catch (\Exception $e) {
            Log::error('Error deleting app storage config: ' . $e->getMessage());
            return redirect()->back()
                           ->with('error', 'حدث خطأ أثناء حذف الإعدادات: ' . $e->getMessage());
        }
    }

    /**
     * اختبار الاتصال
     */
    public function test(AppStorageConfig $config)
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
