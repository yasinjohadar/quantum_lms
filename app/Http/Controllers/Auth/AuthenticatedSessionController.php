<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\LoginRequest;
use App\Services\LoginLogService;
use App\Services\UserSessionService;
use App\Services\SessionActivityService;
use App\Services\AuditLogService;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class AuthenticatedSessionController extends Controller
{
    protected $sessionService;
    protected $activityService;
    protected AuditLogService $auditLogService;

    public function __construct(
        UserSessionService $sessionService,
        SessionActivityService $activityService,
        AuditLogService $auditLogService
    ) {
        $this->sessionService = $sessionService;
        $this->activityService = $activityService;
        $this->auditLogService = $auditLogService;
    }

    /**
     * Display the login view.
     */
    public function create(): View
    {
        return view('auth.login');
    }

    /**
     * Display the student login view.
     */
    public function createStudent(): View
    {
        return view('auth.student-login');
    }

    /**
     * Handle an incoming authentication request.
     */
    public function store(LoginRequest $request): RedirectResponse
    {
        return $this->attemptLogin($request, false);
    }

    /**
     * Handle an incoming student authentication request.
     */
    public function storeStudent(LoginRequest $request): RedirectResponse
    {
        return $this->attemptLogin($request, true);
    }

    private function attemptLogin(LoginRequest $request, bool $studentOnly): RedirectResponse
    {
        try {
            $request->authenticate();
        } catch (\Illuminate\Validation\ValidationException $e) {
            // تسجيل محاولة دخول فاشلة
            LoginLogService::logLogin(null, $request, false, 'Invalid credentials');
            $this->auditLogService->logLoginAttempt(null, false, ['reason' => 'invalid_credentials'], $request);
            throw $e;
        }

        // التحقق من أن المستخدم نشط
        $user = Auth::user();

        if ($studentOnly && !$user->hasRole('student')) {
            LoginLogService::logLogin($user, $request, false, 'Non-student user tried student login');
            $this->auditLogService->logLoginAttempt($user, false, ['reason' => 'non_student_on_student_login'], $request);

            Auth::logout();
            $request->session()->invalidate();
            $request->session()->regenerateToken();

            return back()->withErrors([
                $request->errorField() => 'هذه الصفحة مخصصة لحسابات الطلاب فقط.',
            ]);
        }

        if (!$user->is_active) {
            // تسجيل محاولة دخول فاشلة
            LoginLogService::logLogin($user, $request, false, 'Account is inactive');
            $this->auditLogService->logLoginAttempt($user, false, ['reason' => 'inactive_account'], $request);
            
            Auth::logout();
            $request->session()->invalidate();
            $request->session()->regenerateToken();
            
            return back()->withErrors([
                $request->errorField() => 'تم إلغاء تفعيل حسابك. يرجى التواصل مع الإدارة.',
            ]);
        }

        // تسجيل محاولة دخول ناجحة
        LoginLogService::logLogin($user, $request, true);
        $this->auditLogService->logLoginAttempt($user, true, [], $request);

        // إنشاء جلسة جديدة
        $userSession = $this->sessionService->createSession($user->id, $request);
        
        // تسجيل بداية الجلسة
        $this->activityService->logSessionStart($userSession->id, $request);
        
        // حفظ session_id في Laravel session
        $request->session()->put('user_session_id', $userSession->id);

        $request->session()->regenerate();

        // توجيه المستخدم حسب صلاحيته
        $hasAdminDashboard = false;
        
        // 1. التحقق من وجود أي دور بـ dashboard_type = 'admin' (إذا كان العمود موجوداً)
        try {
            $hasAdminDashboard = $user->roles()
                ->where('dashboard_type', 'admin')
                ->exists();
        } catch (\Exception $e) {
            // إذا كان العمود غير موجود، نستخدم fallback
            $hasAdminDashboard = false;
        }

        if ($hasAdminDashboard) {
            // التحقق إذا كان المستخدم مشرف
            if ($user->hasRole('supervisor')) {
                return redirect()->route('admin.my-classes');
            }
            return redirect()->route('admin.dashboard');
        }

        // 2. Fallback: التحقق من أسماء الأدوار (للتوافق مع البيانات القديمة أو عند عدم وجود العمود)
        if ($user->hasRole(['admin', 'supervisor', 'teacher'])) {
            // التحقق إذا كان المستخدم مشرف
            if ($user->hasRole('supervisor')) {
                return redirect()->route('admin.my-classes');
            }
            return redirect()->route('admin.dashboard');
        }

        // افتراضي: student dashboard
        return redirect()->route('student.dashboard');
    }

    /**
     * Destroy an authenticated session.
     */
    public function destroy(Request $request): RedirectResponse
    {
        $user = Auth::user();
        $sessionId = $request->session()->getId();

        // إنهاء جميع الجلسات النشطة للمستخدم
        if ($user) {
            // الحصول على الجلسات النشطة قبل إنهائها
            $activeSessions = \App\Models\UserSession::where('user_id', $user->id)
                ->where('status', 'active')
                ->get();
            
            foreach ($activeSessions as $activeSession) {
                // تسجيل نهاية الجلسة
                $this->activityService->logSessionEnd($activeSession->id, $request);
            }
            
            $this->sessionService->endAllActiveSessions($user->id, 'completed');
            LoginLogService::logLogout($user->id, $sessionId);
        }

        Auth::guard('web')->logout();

        $request->session()->invalidate();

        $request->session()->regenerateToken();

        return redirect()->route($this->logoutRedirectRouteName($user));
    }

    /**
     * بعد تسجيل الخروج: المستخدمون كالطالب (بدون لوحة إدارة/معلم) → بوابة الطلاب؛ غير ذلك → تسجيل الدخول العام.
     */
    private function logoutRedirectRouteName(?User $user): string
    {
        if ($user === null) {
            return 'login';
        }

        $hasAdminDashboard = false;
        try {
            $hasAdminDashboard = $user->roles()
                ->where('dashboard_type', 'admin')
                ->exists();
        } catch (\Exception $e) {
            $hasAdminDashboard = false;
        }

        if ($hasAdminDashboard) {
            return 'login';
        }

        if ($user->hasRole(['admin', 'supervisor', 'teacher'])) {
            return 'login';
        }

        return 'student.login';
    }
}
