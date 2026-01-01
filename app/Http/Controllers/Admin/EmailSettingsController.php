<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Services\Email\EmailSettingsService;
use App\Services\Email\EmailService;
use App\Mail\TestEmail;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Log;

class EmailSettingsController extends Controller
{
    public function __construct(
        private EmailSettingsService $settingsService,
        private EmailService $emailService
    ) {}

    /**
     * عرض صفحة الإعدادات
     */
    public function index()
    {
        // Initialize defaults if not exists
        $this->settingsService->initializeDefaults();
        
        $settings = $this->settingsService->getSettings();
        
        return view('admin.pages.email-settings.index', compact('settings'));
    }

    /**
     * تحديث الإعدادات
     */
    public function update(Request $request)
    {
        $validated = $request->validate([
            'mail_driver' => 'required|in:smtp,sendmail,log',
            'smtp_host' => 'required_if:mail_driver,smtp|nullable|string|max:255',
            'smtp_port' => 'required_if:mail_driver,smtp|nullable|integer|min:1|max:65535',
            'smtp_encryption' => 'required_if:mail_driver,smtp|nullable|in:none,tls,ssl',
            'smtp_username' => 'nullable|string|max:255',
            'smtp_password' => 'nullable|string|max:255',
            'mail_from_address' => 'required|email|max:255',
            'mail_from_name' => 'required|string|max:255',
            'mail_reply_to' => 'nullable|email|max:255',
        ], [
            'mail_driver.required' => 'نوع البريد مطلوب',
            'mail_driver.in' => 'نوع البريد المحدد غير صالح',
            'smtp_host.required_if' => 'خادم SMTP مطلوب',
            'smtp_port.required_if' => 'منفذ SMTP مطلوب',
            'smtp_port.integer' => 'منفذ SMTP يجب أن يكون رقماً',
            'smtp_encryption.required_if' => 'نوع التشفير مطلوب',
            'mail_from_address.required' => 'عنوان المرسل مطلوب',
            'mail_from_address.email' => 'عنوان المرسل يجب أن يكون بريد إلكتروني صحيح',
            'mail_from_name.required' => 'اسم المرسل مطلوب',
        ]);

        try {
            // If password is empty, keep existing password
            if (empty($validated['smtp_password'])) {
                $existingSettings = $this->settingsService->getSettings();
                $validated['smtp_password'] = $existingSettings['smtp_password'] ?? '';
            }

            $this->settingsService->updateSettings($validated);

            return redirect()->route('admin.email-settings.index')
                           ->with('success', 'تم حفظ الإعدادات بنجاح.');
        } catch (\Exception $e) {
            Log::error('Error updating email settings: ' . $e->getMessage());
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
            $host = $request->input('smtp_host');
            $port = $request->input('smtp_port', '587');
            $encryption = $request->input('smtp_encryption', 'tls');
            $username = $request->input('smtp_username');
            $password = $request->input('smtp_password');

            // If form data provided, test with it, otherwise use saved settings
            if ($host) {
                // Test connection using fsockopen
                $connection = @fsockopen(
                    $host,
                    (int) $port,
                    $errno,
                    $errstr,
                    10
                );

                if (!$connection) {
                    return response()->json([
                        'success' => false,
                        'message' => "فشل الاتصال بالخادم: {$errstr} ({$errno})",
                    ]);
                }

                fclose($connection);

                return response()->json([
                    'success' => true,
                    'message' => 'تم الاتصال بالخادم بنجاح',
                ]);
            } else {
                // Use saved settings
                $result = $this->settingsService->testConnection();
                return response()->json($result);
            }
        } catch (\Exception $e) {
            Log::error('Error testing email connection: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'حدث خطأ: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * إرسال إيميل تجريبي
     */
    public function sendTestEmail(Request $request)
    {
        $validated = $request->validate([
            'email' => 'required|email',
            'message' => 'nullable|string',
        ]);

        try {
            // Get settings from form if provided, otherwise use saved settings
            $smtpHost = $request->input('smtp_host');
            $settingsToApply = null;
            
            if ($smtpHost) {
                // Use form settings temporarily (without saving)
                $settingsToApply = [
                    'mail_driver' => $request->input('mail_driver', 'smtp'),
                    'smtp_host' => $smtpHost,
                    'smtp_port' => $request->input('smtp_port', '587'),
                    'smtp_encryption' => $request->input('smtp_encryption', 'tls'),
                    'smtp_username' => $request->input('smtp_username', ''),
                    'smtp_password' => $request->input('smtp_password', ''),
                    'mail_from_address' => $request->input('mail_from_address', config('mail.from.address')),
                    'mail_from_name' => $request->input('mail_from_name', config('mail.from.name')),
                    'mail_reply_to' => $request->input('mail_reply_to', ''),
                ];
                
                // If password is empty, get from saved settings
                if (empty($settingsToApply['smtp_password'])) {
                    $savedSettings = $this->settingsService->getSettings();
                    $settingsToApply['smtp_password'] = $savedSettings['smtp_password'] ?? '';
                }
            }
            
            // Apply settings to config before sending (temporarily if provided)
            $this->settingsService->applyToConfig($settingsToApply);
            
            $message = $validated['message'] ?? 'هذا إيميل اختبار من نظام Quantum LMS';
            
            Mail::to($validated['email'])->send(new TestEmail($message));

            return response()->json([
                'success' => true,
                'message' => 'تم إرسال الإيميل بنجاح',
            ]);
        } catch (\Exception $e) {
            Log::error('Error sending test email: ' . $e->getMessage());
            Log::error('Error stack: ' . $e->getTraceAsString());
            return response()->json([
                'success' => false,
                'message' => 'فشل إرسال الإيميل: ' . $e->getMessage(),
            ], 500);
        }
    }
}
