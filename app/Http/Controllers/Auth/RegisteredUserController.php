<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Helpers\PhoneHelper;
use App\Models\User;
use App\Models\SystemSetting;
use App\Services\SMS\OTPService;
use Illuminate\Auth\Events\Registered;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules;
use Illuminate\View\View;

class RegisteredUserController extends Controller
{
    public function __construct(
        private OTPService $otpService
    ) {}

    /**
     * Display the registration view.
     */
    public function create(): View
    {
        $phoneVerificationEnabled = SystemSetting::get('phone_verification_enabled', false);
        return view('auth.register', compact('phoneVerificationEnabled'));
    }

    /**
     * Handle an incoming registration request.
     *
     * @throws \Illuminate\Validation\ValidationException
     */
    public function store(Request $request): RedirectResponse
    {
        $phoneVerificationEnabled = SystemSetting::get('phone_verification_enabled', false);

        // تطبيع رقم الهاتف تلقائياً: إضافة + ورمز الدولة إن لزم (مثل 0501234567 → +966501234567)
        if ($request->filled('phone')) {
            $normalized = PhoneHelper::normalize($request->phone, config('app.phone_default_country_code', '966'));
            if ($normalized !== null) {
                $request->merge(['phone' => $normalized]);
            } else {
                $request->merge(['phone' => preg_replace('/\s+/', '', trim($request->phone))]);
            }
        }

        // التحقق من التفرد تجاهل المستخدمين المحذوفين (soft-deleted) حتى لا يظهر "مستخدم بالفعل" لرقم/بريد سبق تسجيله ثم حذف
        $validationRules = [
            'name' => ['required', 'string', 'max:255'],
            'email' => [
                'required',
                'string',
                'lowercase',
                'email',
                'max:255',
                Rule::unique('users', 'email')->whereNull('deleted_at'),
            ],
            'password' => ['required', 'confirmed', Rules\Password::defaults()],
        ];

        if ($phoneVerificationEnabled) {
            $validationRules['phone'] = [
                'required',
                'string',
                'regex:/^\+[1-9]\d{1,14}$/',
                Rule::unique('users', 'phone')->whereNull('deleted_at'),
            ];
        }

        $validated = $request->validate($validationRules, [
            'phone.required' => 'رقم الهاتف مطلوب',
            'phone.regex' => 'رقم الهاتف يجب أن يبدأ بـ + متبوعاً برمز الدولة',
            'phone.unique' => 'رقم الهاتف مستخدم بالفعل',
        ]);

        // إنشاء المستخدم
        $userData = [
            'name' => $validated['name'],
            'email' => $validated['email'],
            'password' => Hash::make($validated['password']),
        ];

        // إذا كانت الميزة مفعلة: تعطيل الحساب حتى يتم التحقق
        if ($phoneVerificationEnabled) {
            $userData['is_active'] = false;
            $userData['phone'] = $validated['phone'];
        } else {
            $userData['is_active'] = true;
            if (isset($validated['phone'])) {
                $userData['phone'] = $validated['phone'];
            }
        }

        $user = User::create($userData);

        // تعيين صلاحية student تلقائياً
        $user->assignRole('student');

        event(new Registered($user));

        // إذا كانت الميزة مفعلة: إرسال OTP و redirect إلى صفحة التحقق
        if ($phoneVerificationEnabled) {
            try {
                // حفظ user_id في session للتحقق لاحقاً
                session(['pending_verification_user_id' => $user->id]);
                
                // إرسال OTP
                Log::info('Generating OTP for user registration', [
                    'user_id' => $user->id,
                    'phone' => $user->phone,
                ]);
                
                $otp = $this->otpService->generateOTP($user, $user->phone, 'verification');
                
                Log::info('OTP generated successfully', [
                    'otp_id' => $otp->id,
                    'phone' => $otp->phone,
                    'expires_at' => $otp->expires_at,
                ]);
                
                // إرسال OTP عبر WhatsApp (افتراضي) أو SMS حسب إعدادات النظام
                $provider = $request->input('otp_provider', SystemSetting::get('otp_provider', 'whatsapp'));
                
                Log::info('Attempting to send OTP', [
                    'provider' => $provider,
                    'phone' => $user->phone,
                ]);
                
                $sent = $this->otpService->sendOTP($otp, $provider);
                
                if (!$sent) {
                    Log::warning('OTP send failed silently', [
                        'user_id' => $user->id,
                        'phone' => $user->phone,
                        'provider' => $provider,
                    ]);
                    
                    return redirect()->route('phone.verify')
                        ->with('warning', 'تم إنشاء حسابك، لكن فشل إرسال رمز التحقق. يرجى المحاولة مرة أخرى من صفحة التحقق.');
                }
                
                Log::info('OTP sent successfully', [
                    'user_id' => $user->id,
                    'phone' => $user->phone,
                    'provider' => $provider,
                ]);

                Log::info('Redirecting to phone verification page', [
                    'user_id' => $user->id,
                    'route' => 'phone.verify',
                ]);

                return redirect()->route('phone.verify')
                    ->with('success', 'تم إرسال رمز التحقق إلى رقم هاتفك. يرجى إدخال الرمز للتحقق من حسابك.');
            } catch (\Exception $e) {
                Log::error('Error sending OTP during registration', [
                    'user_id' => $user->id,
                    'phone' => $user->phone ?? 'N/A',
                    'error' => $e->getMessage(),
                    'trace' => $e->getTraceAsString(),
                ]);
                
                // في حالة فشل الإرسال، يمكن السماح بالتسجيل مع تحذير
                return redirect()->route('phone.verify')
                    ->with('warning', 'تم إنشاء حسابك، لكن فشل إرسال رمز التحقق: ' . $e->getMessage() . '. يرجى المحاولة مرة أخرى من صفحة التحقق.');
            }
        }

        // إذا كانت الميزة معطلة: تسجيل دخول تلقائي
        Auth::login($user);

        // توجيه الطالب إلى لوحة تحكم الطالب
        return redirect(route('student.dashboard', absolute: false));
    }
}