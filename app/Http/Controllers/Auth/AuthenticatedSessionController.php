<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\LoginRequest;
use App\Services\LoginLogService;
use App\Services\UserSessionService;
use App\Services\SessionActivityService;
use App\Services\AuditLogService;
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
     * Handle an incoming authentication request.
     */
    public function store(LoginRequest $request): RedirectResponse
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
        if (!$user->is_active) {
            // تسجيل محاولة دخول فاشلة
            LoginLogService::logLogin($user, $request, false, 'Account is inactive');
            $this->auditLogService->logLoginAttempt($user, false, ['reason' => 'inactive_account'], $request);
            
            Auth::logout();
            $request->session()->invalidate();
            $request->session()->regenerateToken();
            
            return back()->withErrors([
                'email' => 'تم إلغاء تفعيل حسابك. يرجى التواصل مع الإدارة.',
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
        // الحصول على جميع الصلاحيات للمستخدم
        $roles = $user->getRoleNames();
        
        // التحقق من admin أولاً
        if ($user->hasRole('admin')) {
            return redirect()->route('admin.dashboard');
        }
        
        // التحقق من student
        if ($user->hasRole('student')) {
            return redirect()->route('student.dashboard');
        }

        // إذا لم يكن لديه صلاحية محددة، يوجه إلى student dashboard كافتراضي
        // (لأن المستخدمين الجدد يحصلون على صلاحية student تلقائياً)
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

        return redirect()->route('login');
    }
}
