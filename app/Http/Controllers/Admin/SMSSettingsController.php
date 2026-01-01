<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Services\SMS\SMSSettingsService;
use App\Services\SMS\SMSProviderFactory;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class SMSSettingsController extends Controller
{
    public function __construct(
        private SMSSettingsService $settingsService
    ) {}

    /**
     * عرض صفحة الإعدادات
     */
    public function index()
    {
        // Initialize defaults if not exists
        $this->settingsService->initializeDefaults();
        
        $settings = $this->settingsService->getSettings();
        $providers = SMSProviderFactory::getAvailableProviders();
        
        return view('admin.pages.sms-settings.index', compact('settings', 'providers'));
    }

    /**
     * تحديث الإعدادات
     */
    public function update(Request $request)
    {
        $validated = $request->validate([
            'sms_enabled' => 'nullable',
            'sms_provider' => 'required|string|in:local_syria,twilio',
            'local_api_url' => 'required_if:sms_provider,local_syria|nullable|url|max:255',
            'local_api_key' => 'required_if:sms_provider,local_syria|nullable|string|max:255',
            'local_sender_id' => 'required_if:sms_provider,local_syria|nullable|string|max:50',
            'twilio_account_sid' => 'required_if:sms_provider,twilio|nullable|string|max:255|regex:/^AC[a-f0-9]{32}$/',
            'twilio_auth_token' => 'required_if:sms_provider,twilio|nullable|string|max:255',
            'twilio_from_number' => 'required_if:sms_provider,twilio|nullable|string|max:20|regex:/^\+[1-9]\d{1,14}$/',
        ], [
            'sms_provider.required' => 'مزود SMS مطلوب',
            'sms_provider.in' => 'مزود SMS المحدد غير مدعوم',
            'local_api_url.required_if' => 'عنوان API مطلوب',
            'local_api_url.url' => 'عنوان API يجب أن يكون رابط صحيح',
            'local_api_key.required_if' => 'مفتاح API مطلوب',
            'local_sender_id.required_if' => 'معرف المرسل مطلوب',
            'twilio_account_sid.required_if' => 'Account SID مطلوب',
            'twilio_account_sid.regex' => 'Account SID يجب أن يبدأ بـ AC متبوعاً بـ 32 حرف',
            'twilio_auth_token.required_if' => 'Auth Token مطلوب',
            'twilio_from_number.required_if' => 'رقم المرسل مطلوب',
            'twilio_from_number.regex' => 'رقم المرسل يجب أن يبدأ بـ + متبوعاً برمز الدولة',
        ]);

        try {
            // Handle checkbox (if not present in request, it's false)
            $validated['sms_enabled'] = $request->has('sms_enabled') ? '1' : '0';

            // If API key or auth token is empty, keep existing values
            if (empty($validated['local_api_key'])) {
                $existingSettings = $this->settingsService->getSettings();
                $validated['local_api_key'] = $existingSettings['local_api_key'] ?? '';
            }
            
            if (empty($validated['twilio_auth_token'])) {
                $existingSettings = $this->settingsService->getSettings();
                $validated['twilio_auth_token'] = $existingSettings['twilio_auth_token'] ?? '';
            }

            $this->settingsService->updateSettings($validated);

            return redirect()->route('admin.sms-settings.index')
                           ->with('success', 'تم حفظ الإعدادات بنجاح.');
        } catch (\Exception $e) {
            Log::error('Error updating SMS settings: ' . $e->getMessage());
            return redirect()->back()
                           ->with('error', 'حدث خطأ أثناء حفظ الإعدادات: ' . $e->getMessage())
                           ->withInput();
        }
    }

    /**
     * اختبار الاتصال
     */
    public function testConnection(Request $request)
    {
        try {
            // Get settings from form or use saved settings
            $apiUrl = $request->input('local_api_url');
            $apiKey = $request->input('local_api_key');
            $senderId = $request->input('local_sender_id');
            $provider = $request->input('sms_provider', 'local_syria');

            // If form data provided, test with it, otherwise use saved settings
            if ($provider === 'twilio') {
                $accountSid = $request->input('twilio_account_sid');
                $authToken = $request->input('twilio_auth_token');
                $fromNumber = $request->input('twilio_from_number');

                if ($accountSid) {
                    // If auth token is empty, get from saved settings
                    if (empty($authToken)) {
                        $savedSettings = $this->settingsService->getSettings();
                        $authToken = $savedSettings['twilio_auth_token'] ?? '';
                    }

                    $config = [
                        'account_sid' => $accountSid,
                        'auth_token' => $authToken,
                        'from_number' => $fromNumber ?? '',
                    ];

                    $smsProvider = SMSProviderFactory::create($provider, $config);
                    $result = $smsProvider->testConnection();
                    
                    return response()->json($result);
                }
            } else if ($apiUrl) {
                // Local Syria provider
                // If API key is empty, get from saved settings
                if (empty($apiKey)) {
                    $savedSettings = $this->settingsService->getSettings();
                    $apiKey = $savedSettings['local_api_key'] ?? '';
                }

                $config = [
                    'api_url' => $apiUrl,
                    'api_key' => $apiKey,
                    'sender_id' => $senderId ?? '',
                ];

                $smsProvider = SMSProviderFactory::create($provider, $config);
                $result = $smsProvider->testConnection();
                
                return response()->json($result);
            }
            
            // Use saved settings
            $settings = $this->settingsService->getSettings();
            $config = $this->settingsService->getProviderConfig();
            $smsProvider = SMSProviderFactory::create($settings['sms_provider'], $config);
            $result = $smsProvider->testConnection();
            
            return response()->json($result);
        } catch (\Exception $e) {
            Log::error('Error testing SMS connection: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'حدث خطأ: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * إرسال رسالة SMS تجريبية
     */
    public function sendTestSMS(Request $request)
    {
        $validated = $request->validate([
            'phone' => 'required|string|max:20',
            'message' => 'nullable|string|max:500',
        ], [
            'phone.required' => 'رقم الهاتف مطلوب',
        ]);

        try {
            $settings = $this->settingsService->getSettings();

            // Check if SMS is enabled
            if (!$settings['sms_enabled']) {
                return response()->json([
                    'success' => false,
                    'message' => 'SMS معطل. يرجى تفعيله أولاً',
                ], 400);
            }

            // Get provider config
            $provider = $request->input('sms_provider', $settings['sms_provider']);
            
            // Get config based on provider
            if ($provider === 'twilio') {
                $accountSid = $request->input('twilio_account_sid', $settings['twilio_account_sid']);
                $authToken = $request->input('twilio_auth_token', $settings['twilio_auth_token']);
                $fromNumber = $request->input('twilio_from_number', $settings['twilio_from_number']);

                // If auth token is empty, get from saved settings
                if (empty($authToken)) {
                    $savedSettings = $this->settingsService->getSettings();
                    $authToken = $savedSettings['twilio_auth_token'] ?? '';
                }

                $config = [
                    'account_sid' => $accountSid,
                    'auth_token' => $authToken,
                    'from_number' => $fromNumber,
                ];
            } else {
                $apiUrl = $request->input('local_api_url', $settings['local_api_url']);
                $apiKey = $request->input('local_api_key', $settings['local_api_key']);
                $senderId = $request->input('local_sender_id', $settings['local_sender_id']);

                // If API key is empty, get from saved settings
                if (empty($apiKey)) {
                    $savedSettings = $this->settingsService->getSettings();
                    $apiKey = $savedSettings['local_api_key'] ?? '';
                }

                $config = [
                    'api_url' => $apiUrl,
                    'api_key' => $apiKey,
                    'sender_id' => $senderId,
                ];
            }

            $smsProvider = SMSProviderFactory::create($provider, $config);

            $message = $validated['message'] ?? 'هذه رسالة تجريبية من نظام Quantum LMS';
            
            $result = $smsProvider->send($validated['phone'], $message);

            if ($result['success']) {
                return response()->json([
                    'success' => true,
                    'message' => 'تم إرسال الرسالة بنجاح إلى ' . $validated['phone'],
                ]);
            } else {
                return response()->json([
                    'success' => false,
                    'message' => $result['message'],
                ], 400);
            }
        } catch (\Exception $e) {
            Log::error('Error sending test SMS: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'حدث خطأ: ' . $e->getMessage(),
            ], 500);
        }
    }
}
