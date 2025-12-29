<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\SystemSetting;
use App\Models\ZoomAccount;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\View\View;
use Illuminate\Http\RedirectResponse;

class ZoomSettingsController extends Controller
{
    /**
     * عرض صفحة إعدادات Zoom
     */
    public function index(): View
    {
        $accounts = ZoomAccount::orderBy('is_default', 'desc')
            ->orderBy('created_at', 'desc')
            ->get();
        
        $settings = SystemSetting::where('group', 'zoom')->get()->keyBy('key');
        
        // Get current config values
        $config = [
            'api_base_url' => config('zoom.api_base_url'),
            'token_cache_ttl' => config('zoom.token_cache_ttl'),
            'join_window_before_minutes' => config('zoom.join_window_before_minutes'),
            'join_window_after_minutes' => config('zoom.join_window_after_minutes'),
            'token_ttl_minutes' => config('zoom.token_ttl_minutes'),
            'token_max_uses' => config('zoom.token_max_uses'),
            'rate_limit_per_user' => config('zoom.rate_limits.per_user'),
            'rate_limit_per_session' => config('zoom.rate_limits.per_session'),
            'rate_limit_per_ip' => config('zoom.rate_limits.per_ip'),
            'enable_device_binding' => config('zoom.security.enable_device_binding'),
            'enable_ip_binding' => config('zoom.security.enable_ip_binding'),
            'ip_prefix_length' => config('zoom.security.ip_prefix_length'),
        ];

        return view('admin.live-sessions.zoom.settings', compact('accounts', 'settings', 'config'));
    }

    /**
     * حفظ إعدادات Zoom
     */
    public function update(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'join_window_before_minutes' => 'required|integer|min:0|max:60',
            'join_window_after_minutes' => 'required|integer|min:0|max:120',
            'token_ttl_minutes' => 'required|integer|min:1|max:60',
            'token_max_uses' => 'required|integer|min:1|max:10',
            'rate_limit_per_user' => 'required|integer|min:1|max:100',
            'rate_limit_per_session' => 'required|integer|min:1|max:500',
            'rate_limit_per_ip' => 'required|integer|min:1|max:200',
            'enable_device_binding' => 'boolean',
            'enable_ip_binding' => 'boolean',
            'ip_prefix_length' => 'required|integer|min:1|max:4',
        ]);

        try {
            // حفظ الإعدادات في SystemSetting
            SystemSetting::set('zoom_join_window_before_minutes', $validated['join_window_before_minutes'], 'integer', 'zoom', 'دقائق قبل بدء الجلسة للسماح بالانضمام');
            SystemSetting::set('zoom_join_window_after_minutes', $validated['join_window_after_minutes'], 'integer', 'zoom', 'دقائق بعد انتهاء الجلسة للسماح بالانضمام');
            SystemSetting::set('zoom_token_ttl_minutes', $validated['token_ttl_minutes'], 'integer', 'zoom', 'مدة صلاحية رمز الانضمام بالدقائق');
            SystemSetting::set('zoom_token_max_uses', $validated['token_max_uses'], 'integer', 'zoom', 'الحد الأقصى لاستخدامات رمز الانضمام');
            SystemSetting::set('zoom_rate_limit_per_user', $validated['rate_limit_per_user'], 'integer', 'zoom', 'حد الطلبات لكل مستخدم في الدقيقة');
            SystemSetting::set('zoom_rate_limit_per_session', $validated['rate_limit_per_session'], 'integer', 'zoom', 'حد الطلبات لكل جلسة في الدقيقة');
            SystemSetting::set('zoom_rate_limit_per_ip', $validated['rate_limit_per_ip'], 'integer', 'zoom', 'حد الطلبات لكل IP في الدقيقة');
            SystemSetting::set('zoom_enable_device_binding', $validated['enable_device_binding'] ?? false, 'boolean', 'zoom', 'تفعيل ربط الجهاز');
            SystemSetting::set('zoom_enable_ip_binding', $validated['enable_ip_binding'] ?? false, 'boolean', 'zoom', 'تفعيل ربط IP');
            SystemSetting::set('zoom_ip_prefix_length', $validated['ip_prefix_length'], 'integer', 'zoom', 'طول بادئة IP للربط');

            return redirect()->route('admin.zoom.settings.index')
                ->with('success', 'تم حفظ إعدادات Zoom بنجاح.');
        } catch (\Exception $e) {
            Log::error('Error updating Zoom settings: ' . $e->getMessage());
            return redirect()->back()
                ->with('error', 'حدث خطأ أثناء حفظ الإعدادات: ' . $e->getMessage())
                ->withInput();
        }
    }

    /**
     * حفظ حساب Zoom جديد أو تحديثه
     */
    public function storeAccount(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'type' => 'required|in:api,oauth',
            'account_id' => 'nullable|string|max:255',
            'client_id' => 'required|string|max:255',
            'client_secret' => 'required|string',
            'sdk_key' => 'nullable|string|max:255',
            'sdk_secret' => 'nullable|string',
            'redirect_uri' => 'nullable|url|max:500',
            'is_default' => 'boolean',
            'is_active' => 'boolean',
            'description' => 'nullable|string',
        ]);

        try {
            // إذا كان نوع API، يجب أن يكون account_id موجود
            if ($validated['type'] === 'api' && empty($validated['account_id'])) {
                return redirect()->back()
                    ->with('error', 'Account ID مطلوب لحساب API')
                    ->withInput();
            }

            $account = ZoomAccount::create([
                'name' => $validated['name'],
                'type' => $validated['type'],
                'account_id' => $validated['account_id'] ?? null,
                'client_id' => $validated['client_id'],
                'client_secret' => $validated['client_secret'],
                'sdk_key' => $validated['sdk_key'] ?? null,
                'sdk_secret' => $validated['sdk_secret'] ?? null,
                'redirect_uri' => $validated['redirect_uri'] ?? null,
                'is_default' => $validated['is_default'] ?? false,
                'is_active' => $validated['is_active'] ?? true,
                'description' => $validated['description'] ?? null,
                'created_by' => $request->user()->id,
            ]);

            // إذا تم تعيينه كافتراضي، قم بإلغاء تعيين الآخرين
            if ($account->is_default) {
                $account->setAsDefault();
            }

            return redirect()->route('admin.zoom.settings.index')
                ->with('success', 'تم إضافة حساب Zoom بنجاح.');
        } catch (\Exception $e) {
            Log::error('Error storing Zoom account: ' . $e->getMessage());
            return redirect()->back()
                ->with('error', 'حدث خطأ أثناء إضافة الحساب: ' . $e->getMessage())
                ->withInput();
        }
    }

    /**
     * تحديث حساب Zoom
     */
    public function updateAccount(Request $request, ZoomAccount $account): RedirectResponse
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'type' => 'required|in:api,oauth',
            'account_id' => 'nullable|string|max:255',
            'client_id' => 'required|string|max:255',
            'client_secret' => 'nullable|string', // nullable لأننا قد لا نريد تغييره
            'sdk_key' => 'nullable|string|max:255',
            'sdk_secret' => 'nullable|string',
            'redirect_uri' => 'nullable|url|max:500',
            'is_default' => 'boolean',
            'is_active' => 'boolean',
            'description' => 'nullable|string',
        ]);

        try {
            // إذا كان نوع API، يجب أن يكون account_id موجود
            if ($validated['type'] === 'api' && empty($validated['account_id'])) {
                return redirect()->back()
                    ->with('error', 'Account ID مطلوب لحساب API')
                    ->withInput();
            }

            // تحديث البيانات
            $account->update([
                'name' => $validated['name'],
                'type' => $validated['type'],
                'account_id' => $validated['account_id'] ?? null,
                'client_id' => $validated['client_id'],
                'sdk_key' => $validated['sdk_key'] ?? null,
                'redirect_uri' => $validated['redirect_uri'] ?? null,
                'is_default' => $validated['is_default'] ?? false,
                'is_active' => $validated['is_active'] ?? true,
                'description' => $validated['description'] ?? null,
            ]);

            // تحديث client_secret فقط إذا تم إرساله
            if (!empty($validated['client_secret'])) {
                $account->client_secret = $validated['client_secret'];
                $account->save();
            }

            // تحديث sdk_secret فقط إذا تم إرساله
            if (!empty($validated['sdk_secret'])) {
                $account->sdk_secret = $validated['sdk_secret'];
                $account->save();
            }

            // إذا تم تعيينه كافتراضي، قم بإلغاء تعيين الآخرين
            if ($account->is_default) {
                $account->setAsDefault();
            }

            return redirect()->route('admin.zoom.settings.index')
                ->with('success', 'تم تحديث حساب Zoom بنجاح.');
        } catch (\Exception $e) {
            Log::error('Error updating Zoom account: ' . $e->getMessage());
            return redirect()->back()
                ->with('error', 'حدث خطأ أثناء تحديث الحساب: ' . $e->getMessage())
                ->withInput();
        }
    }

    /**
     * حذف حساب Zoom
     */
    public function deleteAccount(ZoomAccount $account): RedirectResponse
    {
        try {
            $account->delete();

            return redirect()->route('admin.zoom.settings.index')
                ->with('success', 'تم حذف حساب Zoom بنجاح.');
        } catch (\Exception $e) {
            Log::error('Error deleting Zoom account: ' . $e->getMessage());
            return redirect()->back()
                ->with('error', 'حدث خطأ أثناء حذف الحساب: ' . $e->getMessage());
        }
    }

    /**
     * تعيين حساب كافتراضي
     */
    public function setDefault(ZoomAccount $account): RedirectResponse
    {
        try {
            $account->setAsDefault();

            return redirect()->route('admin.zoom.settings.index')
                ->with('success', 'تم تعيين الحساب كافتراضي بنجاح.');
        } catch (\Exception $e) {
            Log::error('Error setting default Zoom account: ' . $e->getMessage());
            return redirect()->back()
                ->with('error', 'حدث خطأ أثناء تعيين الحساب الافتراضي: ' . $e->getMessage());
        }
    }
}
