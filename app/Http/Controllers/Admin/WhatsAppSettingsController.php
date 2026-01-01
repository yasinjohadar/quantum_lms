<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Exceptions\WhatsAppApiException;
use App\Services\WhatsApp\WhatsAppClient;
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
            'api_version' => 'required|string|max:10',
            'phone_number_id' => 'required|string|max:255',
            'waba_id' => 'nullable|string|max:255',
            'access_token' => 'nullable|string|max:500',
            'verify_token' => 'required|string|max:255',
            'app_secret' => 'nullable|string|max:255',
            'default_from' => 'nullable|string|max:50',
            'strict_signature' => 'nullable',
            'auto_reply' => 'nullable',
            'auto_reply_message' => 'nullable|string|max:500',
        ], [
            'api_version.required' => 'إصدار API مطلوب',
            'phone_number_id.required' => 'معرف رقم الهاتف مطلوب',
            'verify_token.required' => 'رمز التحقق مطلوب',
        ]);

        try {
            // Handle checkboxes
            $validated['whatsapp_enabled'] = $request->has('whatsapp_enabled') ? '1' : '0';
            $validated['strict_signature'] = $request->has('strict_signature') ? '1' : '0';
            $validated['auto_reply'] = $request->has('auto_reply') ? '1' : '0';

            // If access_token or app_secret is empty, keep existing values
            if (empty($validated['access_token'])) {
                $existingSettings = $this->settingsService->getSettings();
                $validated['access_token'] = $existingSettings['access_token'] ?? '';
            }

            if (empty($validated['app_secret'])) {
                $existingSettings = $this->settingsService->getSettings();
                $validated['app_secret'] = $existingSettings['app_secret'] ?? '';
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
            $phoneNumberId = $request->input('phone_number_id');
            $accessToken = $request->input('access_token');

            // Use form values or saved settings
            if (empty($phoneNumberId)) {
                $settings = $this->settingsService->getSettings();
                $phoneNumberId = $settings['phone_number_id'] ?? '';
            }

            if (empty($accessToken)) {
                $settings = $this->settingsService->getSettings();
                $accessToken = $settings['access_token'] ?? '';
            }

            if (empty($phoneNumberId) || empty($accessToken)) {
                return response()->json([
                    'success' => false,
                    'message' => 'معرف رقم الهاتف و Access Token مطلوبان',
                ], 400);
            }

            // Test connection by making a simple API call (get phone number info)
            $apiVersion = config('whatsapp.api_version', 'v20.0');
            $baseUrl = config('whatsapp.base_url', 'https://graph.facebook.com');
            $url = "{$baseUrl}/{$apiVersion}/{$phoneNumberId}";

            $response = \Illuminate\Support\Facades\Http::timeout(10)
                ->withToken($accessToken)
                ->get($url);

            if ($response->successful()) {
                $data = $response->json();
                return response()->json([
                    'success' => true,
                    'message' => 'تم الاتصال بنجاح. ' . ($data['display_phone_number'] ?? ''),
                ]);
            } else {
                $errorData = $response->json();
                $errorMessage = $errorData['error']['message'] ?? 'فشل الاتصال';
                return response()->json([
                    'success' => false,
                    'message' => 'فشل الاتصال: ' . $errorMessage,
                ], 500);
            }
        } catch (\Exception $e) {
            Log::error('Error testing WhatsApp connection: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'حدث خطأ: ' . $e->getMessage(),
            ], 500);
        }
    }
}
