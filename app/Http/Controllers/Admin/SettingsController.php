<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\SystemSetting;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\View\View;
use Illuminate\Http\RedirectResponse;

class SettingsController extends Controller
{
    /**
     * عرض صفحة الإعدادات العامة
     */
    public function index(Request $request): View
    {
        $group = $request->get('group', 'general');
        
        $settings = SystemSetting::where('group', $group)
            ->orderBy('key')
            ->get()
            ->keyBy('key');
        
        $groups = SystemSetting::GROUPS;
        
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
                $setting = SystemSetting::where('key', $key)->first();
                
                if ($setting) {
                    $setting->value = $value;
                    $setting->save();
                } else {
                    // إنشاء إعداد جديد إذا لم يكن موجوداً
                    SystemSetting::set(
                        $key,
                        $value,
                        'string',
                        $validated['group'] ?? 'general'
                    );
                }
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

