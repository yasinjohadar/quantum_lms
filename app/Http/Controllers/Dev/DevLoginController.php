<?php

namespace App\Http\Controllers\Dev;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Services\LoginLogService;
use App\Services\SessionActivityService;
use App\Services\UserSessionService;
use App\Support\DevLogin;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

/**
 * بوابة الدخول السريع للمطورين — بيئة التطوير فقط.
 *
 * المسارات تُسجَّل فقط عندما تكون DevLogin::enabled() صحيحة (routes/dev.php)،
 * ومع ذلك نتحقق مرة أخرى في كل إجراء كطبقة حماية إضافية.
 */
class DevLoginController extends Controller
{
    public function __construct(
        private UserSessionService $sessionService,
        private SessionActivityService $activityService
    ) {
    }

    /**
     * لوحة الحسابات التجريبية.
     */
    public function index()
    {
        $this->ensureEnabled();

        return view('dev.login', [
            'accounts' => DevLogin::accounts(),
            'password' => DevLogin::password(),
        ]);
    }

    /**
     * تسجيل الدخول فوراً بحساب تجريبي بدون إدخال بيانات.
     */
    public function loginAs(Request $request, string $key)
    {
        $this->ensureEnabled();

        $account = DevLogin::account($key);

        if ($account === null) {
            abort(404);
        }

        $user = DevLogin::userFor($account);

        if ($user === null) {
            return redirect()->route('dev.login')->with(
                'dev_error',
                'الحساب ' . $account['email'] . ' غير موجود. اضغط «إنشاء/تحديث الحسابات التجريبية» أو نفّذ: php artisan db:seed --class=DevAccountsSeeder'
            );
        }

        // في بيئة التطوير: أعِد تفعيل الحساب تلقائياً إن كان معطلاً
        if (! $user->is_active) {
            $user->forceFill(['is_active' => true])->save();
        }

        if (Auth::check()) {
            Auth::logout();
        }

        Auth::login($user, true);

        $this->registerSession($request, $user);

        $request->session()->regenerate();

        return redirect()->route('dashboard')->with(
            'status',
            'تم الدخول كـ ' . $account['label'] . ' (' . $user->email . ') عبر بوابة التطوير.'
        );
    }

    /**
     * تشغيل seeder الحسابات التجريبية من المتصفح.
     */
    public function seed()
    {
        $this->ensureEnabled();

        try {
            Artisan::call('db:seed', [
                '--class' => \Database\Seeders\DevAccountsSeeder::class,
                '--force' => true,
            ]);

            return redirect()->route('dev.login')->with('dev_status', 'تم إنشاء/تحديث الحسابات التجريبية بنجاح.');
        } catch (\Throwable $e) {
            Log::error('DevLogin seed failed', ['error' => $e->getMessage()]);

            return redirect()->route('dev.login')->with('dev_error', 'فشل تشغيل الـ seeder: ' . $e->getMessage());
        }
    }

    /**
     * تسجيل الجلسة بنفس طريقة تسجيل الدخول العادي (حتى تعمل شاشات الجلسات والسجلات).
     */
    private function registerSession(Request $request, User $user): void
    {
        try {
            LoginLogService::logLogin($user, $request, true);

            $userSession = $this->sessionService->createSession($user->id, $request);
            $this->activityService->logSessionStart($userSession->id, $request);
            $request->session()->put('user_session_id', $userSession->id);
        } catch (\Throwable $e) {
            // لا نُفشل الدخول في بيئة التطوير بسبب سجلات الجلسات
            Log::warning('DevLogin session logging skipped', ['error' => $e->getMessage()]);
        }
    }

    private function ensureEnabled(): void
    {
        abort_unless(DevLogin::enabled(), 404);
    }
}
