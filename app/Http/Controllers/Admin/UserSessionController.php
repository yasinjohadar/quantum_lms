<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\UserSession;
use App\Models\User;
use App\Models\SessionActivity;
use App\Services\UserSessionService;
use App\Services\SessionActivityService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class UserSessionController extends Controller
{
    protected $sessionService;
    protected $activityService;

    public function __construct(UserSessionService $sessionService, SessionActivityService $activityService)
    {
        $this->sessionService = $sessionService;
        $this->activityService = $activityService;
    }

    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $sessionsQuery = UserSession::with('user');

        // فلترة حسب البحث
        if ($request->filled('search')) {
            $sessionsQuery->search($request->input('search'));
        }

        // فلترة حسب المستخدم
        if ($request->filled('user_id')) {
            $sessionsQuery->forUser($request->input('user_id'));
        }

        // فلترة حسب IP
        if ($request->filled('ip_address')) {
            $sessionsQuery->forIp($request->input('ip_address'));
        }

        // فلترة حسب الحالة
        if ($request->filled('status')) {
            $sessionsQuery->byStatus($request->input('status'));
        }

        // فلترة حسب نوع الجهاز
        if ($request->filled('device_type')) {
            $sessionsQuery->where('device_type', $request->input('device_type'));
        }

        // فلترة حسب التاريخ
        if ($request->filled('date_from')) {
            $dateFrom = $request->input('date_from');
            $dateTo = $request->input('date_to');
            $sessionsQuery->dateRange($dateFrom, $dateTo);
        }

        $sessions = $sessionsQuery->latest('started_at')->paginate(20);
        
        // إحصائيات
        $stats = [
            'total' => UserSession::count(),
            'active' => UserSession::active()->count(),
            'completed' => UserSession::completed()->count(),
            'disconnected' => UserSession::disconnected()->count(),
            'timeout' => UserSession::timeout()->count(),
            'today' => UserSession::whereDate('started_at', today())->count(),
            'unique_ips' => UserSession::distinct('ip_address')->count('ip_address'),
            'total_duration' => UserSession::whereNotNull('duration_seconds')->sum('duration_seconds'),
        ];

        // جلب المستخدمين للفلترة
        $users = User::orderBy('name')->limit(100)->get();

        return view('admin.pages.user-sessions.index', compact('sessions', 'stats', 'users'));
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        try {
            $session = UserSession::with(['user', 'activities'])->findOrFail($id);
            
            // جلب الأنشطة مع pagination
            $activities = SessionActivity::where('user_session_id', $id)
                ->latest('occurred_at')
                ->paginate(50);
            
            // إحصائيات الأنشطة
            $activityStats = $this->activityService->getSessionStats($id);
            
            return view('admin.pages.user-sessions.show', compact('session', 'activities', 'activityStats'));
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            return redirect()->route('admin.user-sessions.index')
                ->with('error', 'الجلسة المطلوبة غير موجودة');
        } catch (\Exception $e) {
            Log::error('Error showing user session: ' . $e->getMessage());
            return redirect()->route('admin.user-sessions.index')
                ->with('error', 'حدث خطأ أثناء عرض الجلسة: ' . $e->getMessage());
        }
    }

    /**
     * عرض أنشطة جلسة معينة
     */
    public function activities(string $id, Request $request)
    {
        try {
            $session = UserSession::with('user')->findOrFail($id);
            
            $activitiesQuery = SessionActivity::where('user_session_id', $id);

            // فلترة حسب نوع النشاط
            if ($request->filled('activity_type')) {
                $activitiesQuery->ofType($request->input('activity_type'));
            }

            // فلترة حسب التاريخ
            if ($request->filled('date_from')) {
                $dateFrom = $request->input('date_from');
                $dateTo = $request->input('date_to') ?? now();
                $activitiesQuery->inTimeRange($dateFrom, $dateTo);
            }

            $activities = $activitiesQuery->latest('occurred_at')->paginate(100);
            
            // إحصائيات
            $stats = $this->activityService->getSessionStats($id);
            
            // أنواع الأنشطة المتاحة
            $activityTypes = [
                'session_start' => 'بداية الجلسة',
                'session_end' => 'نهاية الجلسة',
                'page_view' => 'عرض صفحة',
                'action' => 'إجراء',
                'disconnect' => 'انقطاع',
                'reconnect' => 'إعادة اتصال',
                'idle_start' => 'بداية الخمول',
                'idle_end' => 'نهاية الخمول',
                'focus_lost' => 'فقدان التركيز',
                'focus_gained' => 'استعادة التركيز',
            ];

            return view('admin.pages.user-sessions.activities', compact('session', 'activities', 'stats', 'activityTypes'));
        } catch (\Exception $e) {
            Log::error('Error showing session activities: ' . $e->getMessage());
            return redirect()->route('admin.user-sessions.show', $id)
                ->with('error', 'حدث خطأ أثناء عرض الأنشطة: ' . $e->getMessage());
        }
    }

    /**
     * عرض جلسات مستخدم معين
     */
    public function userSessions(string $userId)
    {
        try {
            $user = User::findOrFail($userId);
            $sessions = UserSession::where('user_id', $userId)
                ->latest('started_at')
                ->paginate(20);

            // إحصائيات للمستخدم
            $stats = [
                'total' => UserSession::where('user_id', $userId)->count(),
                'active' => UserSession::where('user_id', $userId)->active()->count(),
                'completed' => UserSession::where('user_id', $userId)->completed()->count(),
                'total_duration' => UserSession::where('user_id', $userId)
                    ->whereNotNull('duration_seconds')
                    ->sum('duration_seconds'),
            ];

            return view('admin.pages.user-sessions.user-sessions', compact('user', 'sessions', 'stats'));
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            return redirect()->route('admin.user-sessions.index')
                ->with('error', 'المستخدم المطلوب غير موجود');
        } catch (\Exception $e) {
            Log::error('Error showing user sessions: ' . $e->getMessage());
            return redirect()->route('admin.user-sessions.index')
                ->with('error', 'حدث خطأ أثناء عرض جلسات المستخدم: ' . $e->getMessage());
        }
    }

    /**
     * إنهاء جلسة نشطة
     */
    public function endSession(Request $request, string $id)
    {
        try {
            $session = UserSession::findOrFail($id);
            
            if ($session->status !== 'active') {
                return redirect()->back()
                    ->with('error', 'الجلسة غير نشطة');
            }

            $status = $request->input('status', 'completed');
            $notes = $request->input('notes');

            $this->sessionService->endSession($id, $status, $notes);

            return redirect()->back()
                ->with('success', 'تم إنهاء الجلسة بنجاح');
        } catch (\Exception $e) {
            Log::error('Error ending session: ' . $e->getMessage());
            return redirect()->back()
                ->with('error', 'حدث خطأ أثناء إنهاء الجلسة: ' . $e->getMessage());
        }
    }

    /**
     * حذف جلسة
     */
    public function destroy(string $id)
    {
        try {
            $session = UserSession::findOrFail($id);
            $session->delete();

            return redirect()->route('admin.user-sessions.index')
                ->with('success', 'تم حذف الجلسة بنجاح');
        } catch (\Exception $e) {
            Log::error('Error deleting session: ' . $e->getMessage());
            return redirect()->back()
                ->with('error', 'حدث خطأ أثناء حذف الجلسة: ' . $e->getMessage());
        }
    }

    /**
     * حذف الجلسات القديمة
     */
    public function clearOld(Request $request)
    {
        try {
            $days = $request->input('days', 30);
            $status = $request->input('status'); // completed, disconnected, timeout

            $query = UserSession::where('started_at', '<', now()->subDays($days));
            
            if ($status) {
                $query->where('status', $status);
            }

            $deleted = $query->delete();

            return redirect()->route('admin.user-sessions.index')
                ->with('success', "تم حذف {$deleted} جلسة قديمة بنجاح");
        } catch (\Exception $e) {
            Log::error('Error clearing old sessions: ' . $e->getMessage());
            return redirect()->back()
                ->with('error', 'حدث خطأ أثناء حذف الجلسات القديمة: ' . $e->getMessage());
        }
    }
}

