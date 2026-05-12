<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\SystemSetting;
use App\Services\Storage\StorageRuntimeConfig;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\View\View;
use Illuminate\Http\RedirectResponse;

class SettingsController extends Controller
{
    public function __construct()
    {
        $this->middleware(['permission:settings-manage'])->only(['index', 'update']);
    }

    /**
     * عرض صفحة الإعدادات العامة
     */
    public function index(Request $request): View|RedirectResponse
    {
        $group = $request->get('group', 'general');

        // إدارة روابط التواصل من صفحة "روابط التواصل الاجتماعي" وليس من هنا
        if ($group === 'social') {
            return redirect()->route('admin.social-links.index');
        }

        $settings = SystemSetting::where('group', $group)
            ->orderBy('key')
            ->get()
            ->keyBy('key');

        $groups = collect(SystemSetting::GROUPS)->except('social')->all();

        return view('admin.pages.settings.index', compact('settings', 'groups', 'group'));
    }

    /**
     * تحديث الإعدادات
     */
    public function update(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'settings' => 'required|array',
            'group' => 'nullable|string',
        ]);

        try {
            foreach ($validated['settings'] as $key => $value) {
                $setting = SystemSetting::where('key', $key)
                    ->where('group', $validated['group'] ?? 'general')
                    ->first();
                
                if ($setting) {
                    // Handle boolean values
                    if ($setting->type === 'boolean') {
                        $setting->value = $value ? '1' : '0';
                    } elseif ($setting->type === 'text') {
                        $setting->value = $value;
                    } else {
                        $setting->value = $value;
                    }
                    $setting->save();
                } else {
                    // إنشاء إعداد جديد إذا لم يكن موجوداً
                    $type = 'string';
                    if ($key === 'phone_verification_enabled') {
                        $type = 'boolean';
                    }
                    SystemSetting::set(
                        $key,
                        $type === 'boolean' ? ($value ? '1' : '0') : $value,
                        $type,
                        $validated['group'] ?? 'general'
                    );
                }
            }

            if (($validated['group'] ?? 'general') === 'storage') {
                StorageRuntimeConfig::resetApplicationCache();
            }

            return redirect()->route('admin.settings.index', ['group' => $validated['group'] ?? 'general'])
                           ->with('success', 'تم حفظ الإعدادات بنجاح.');
        } catch (\Exception $e) {
            Log::error('Error updating settings: ' . $e->getMessage());
            return redirect()->back()
                ->with('error', 'حدث خطأ أثناء حفظ الإعدادات: ' . $e->getMessage())
                ->withInput();
        }
    }

    /**
     * إعادة تعيين إعدادات مجموعة معينة
     */
    public function reset(Request $request, string $group): RedirectResponse
    {
        try {
            SystemSetting::where('group', $group)->delete();

            return redirect()->route('admin.settings.index', ['group' => $group])
                ->with('success', 'تم إعادة تعيين الإعدادات بنجاح.');
        } catch (\Exception $e) {
            Log::error('Error resetting settings: ' . $e->getMessage());
            return redirect()->back()
                ->with('error', 'حدث خطأ أثناء إعادة تعيين الإعدادات: ' . $e->getMessage());
        }
    }
}




