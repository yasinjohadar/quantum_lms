<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Exceptions\WhatsAppApiException;
use App\Services\WhatsApp\WhatsAppProviderFactory;
use App\Services\WhatsApp\WhatsAppSettingsService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class WhatsAppSettingsController extends Controller
{
    public function __construct(
        private WhatsAppSettingsService $settingsService
    ) {}

    /**
     * Display settings page
     */
    public function index()
    {
        $this->settingsService->initializeDefaults();
        $settings = $this->settingsService->getSettings();

        return view('admin.pages.whatsapp-settings.index', compact('settings'));
    }

    /**
     * Update settings
     */
    public function update(Request $request)
    {
        $validated = $request->validate([
            'whatsapp_enabled' => 'nullable',
            'whatsapp_provider' => 'required|string|in:meta,custom_api',
            'api_version' => 'required_if:whatsapp_provider,meta|nullable|string|max:10',
            'phone_number_id' => 'required_if:whatsapp_provider,meta|nullable|string|max:255',
            'waba_id' => 'nullable|string|max:255',
            'access_token' => 'nullable|string|max:500',
            'verify_token' => 'required_if:whatsapp_provider,meta|nullable|string|max:255',
            'app_secret' => 'nullable|string|max:255',
            'webhook_path' => 'nullable|string|max:255',
            'default_from' => 'nullable|string|max:50',
            'strict_signature' => 'nullable',
            'auto_reply' => 'nullable',
            'auto_reply_message' => 'nullable|string|max:500',
            'timeout' => 'nullable|integer|min:1|max:300',
            'custom_api_url' => 'required_if:whatsapp_provider,custom_api|nullable|string|url|max:500',
            'custom_api_key' => 'nullable|string|max:500',
            'custom_api_method' => 'nullable|string|in:GET,POST',
            'custom_api_headers' => 'nullable|string|max:1000',
        ], [
            'whatsapp_provider.required' => 'نوع المزود مطلوب',
            'whatsapp_provider.in' => 'نوع المزود غير صالح',
            'api_version.required_if' => 'إصدار API مطلوب للمزود Meta',
            'phone_number_id.required_if' => 'معرف رقم الهاتف مطلوب للمزود Meta',
            'verify_token.required_if' => 'رمز التحقق مطلوب للمزود Meta',
            'custom_api_url.required_if' => 'رابط API مطلوب للمزود المخصص',
            'custom_api_url.url' => 'رابط API غير صالح',
            'timeout.integer' => 'المهلة الزمنية يجب أن تكون رقماً',
            'timeout.min' => 'المهلة الزمنية يجب أن تكون على الأقل ثانية واحدة',
            'timeout.max' => 'المهلة الزمنية يجب أن تكون أقل من 300 ثانية',
        ]);

        try {
            // Handle checkboxes
            $validated['whatsapp_enabled'] = $request->has('whatsapp_enabled') ? '1' : '0';
            $validated['strict_signature'] = $request->has('strict_signature') ? '1' : '0';
            $validated['auto_reply'] = $request->has('auto_reply') ? '1' : '0';

            // If access_token, app_secret, or custom_api_key is empty, keep existing values
            if (empty($validated['access_token'])) {
                $existingSettings = $this->settingsService->getSettings();
                $validated['access_token'] = $existingSettings['access_token'] ?? '';
            }

            if (empty($validated['app_secret'])) {
                $existingSettings = $this->settingsService->getSettings();
                $validated['app_secret'] = $existingSettings['app_secret'] ?? '';
            }

            if (empty($validated['custom_api_key'])) {
                $existingSettings = $this->settingsService->getSettings();
                $validated['custom_api_key'] = $existingSettings['custom_api_key'] ?? '';
            }

            $this->settingsService->updateSettings($validated);

            return redirect()->route('admin.whatsapp-settings.index')
                           ->with('success', 'تم حفظ الإعدادات بنجاح.');
        } catch (\Exception $e) {
            Log::error('Error updating WhatsApp settings: ' . $e->getMessage());
            return redirect()->back()
                           ->with('error', 'حدث خطأ أثناء حفظ الإعدادات: ' . $e->getMessage())
                           ->withInput();
        }
    }

    /**
     * Test connection to WhatsApp API
     */
    public function testConnection(Request $request)
    {
        try {
            $settings = $this->settingsService->getSettings();
            $provider = $request->input('whatsapp_provider', $settings['whatsapp_provider'] ?? 'meta');

            // Get provider config
            if ($provider === 'custom_api') {
                $config = [
                    'api_url' => $request->input('custom_api_url', $settings['custom_api_url'] ?? ''),
                    'api_key' => $request->input('custom_api_key', $settings['custom_api_key'] ?? ''),
                    'api_method' => $request->input('custom_api_method', $settings['custom_api_method'] ?? 'POST'),
                    'headers' => $this->parseHeaders($request->input('custom_api_headers', $settings['custom_api_headers'] ?? [])),
                ];
            } else {
                $config = [
                    'api_version' => $request->input('api_version', $settings['api_version'] ?? 'v20.0'),
                    'phone_number_id' => $request->input('phone_number_id', $settings['phone_number_id'] ?? ''),
                    'access_token' => $request->input('access_token', $settings['access_token'] ?? ''),
                ];
            }

            // Create provider and test connection
            $providerInstance = WhatsAppProviderFactory::create($provider, $config);
            $result = $providerInstance->testConnection();

            return response()->json($result, $result['success'] ? 200 : 500);
        } catch (\Exception $e) {
            Log::error('Error testing WhatsApp connection: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'حدث خطأ: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Parse headers from JSON string or array
     */
    protected function parseHeaders($headers): array
    {
        if (is_array($headers)) {
            return $headers;
        }

        if (is_string($headers)) {
            try {
                $decoded = json_decode($headers, true);
                return is_array($decoded) ? $decoded : [];
            } catch (\Exception $e) {
                return [];
            }
        }

        return [];
    }
}
