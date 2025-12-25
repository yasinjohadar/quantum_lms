<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\LoginLog;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class LoginLogController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $logsQuery = LoginLog::with('user');

        // فلترة حسب البحث
        if ($request->filled('search')) {
            $logsQuery->search($request->input('search'));
        }

        // فلترة حسب المستخدم
        if ($request->filled('user_id')) {
            $logsQuery->forUser($request->input('user_id'));
        }

        // فلترة حسب IP
        if ($request->filled('ip_address')) {
            $logsQuery->forIp($request->input('ip_address'));
        }

        // فلترة حسب الحالة
        if ($request->filled('is_successful')) {
            $logsQuery->where('is_successful', $request->boolean('is_successful'));
        }

        // فلترة حسب التاريخ
        if ($request->filled('date_from')) {
            $dateFrom = $request->input('date_from');
            $dateTo = $request->input('date_to');
            $logsQuery->dateRange($dateFrom, $dateTo);
        }

        $logs = $logsQuery->latest('login_at')->paginate(20);
        
        // إحصائيات
        $stats = [
            'total' => LoginLog::count(),
            'successful' => LoginLog::successful()->count(),
            'failed' => LoginLog::failed()->count(),
            'today' => LoginLog::whereDate('login_at', today())->count(),
            'unique_ips' => LoginLog::distinct('ip_address')->count('ip_address'),
        ];

        // جلب المستخدمين للفلترة
        $users = User::orderBy('name')->limit(100)->get();

        return view('admin.pages.login-logs.index', compact('logs', 'stats', 'users'));
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        try {
            $log = LoginLog::with('user')->findOrFail($id);
            return view('admin.pages.login-logs.show', compact('log'));
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            return redirect()->route('admin.login-logs.index')
                ->with('error', 'سجل الدخول المطلوب غير موجود');
        } catch (\Exception $e) {
            Log::error('Error showing login log: ' . $e->getMessage());
            return redirect()->route('admin.login-logs.index')
                ->with('error', 'حدث خطأ أثناء عرض سجل الدخول: ' . $e->getMessage());
        }
    }

    /**
     * عرض سجلات مستخدم معين
     */
    public function userLogs(string $userId)
    {
        try {
            $user = User::findOrFail($userId);
            $logs = LoginLog::where('user_id', $userId)
                ->latest('login_at')
                ->paginate(20);

            return view('admin.pages.login-logs.user-logs', compact('user', 'logs'));
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            return redirect()->route('admin.login-logs.index')
                ->with('error', 'المستخدم المطلوب غير موجود');
        } catch (\Exception $e) {
            Log::error('Error showing user logs: ' . $e->getMessage());
            return redirect()->route('admin.login-logs.index')
                ->with('error', 'حدث خطأ أثناء عرض سجلات المستخدم: ' . $e->getMessage());
        }
    }

    /**
     * عرض سجلات IP معين
     */
    public function ipLogs(string $ip)
    {
        try {
            $logs = LoginLog::where('ip_address', $ip)
                ->with('user')
                ->latest('login_at')
                ->paginate(20);

            return view('admin.pages.login-logs.ip-logs', compact('ip', 'logs'));
        } catch (\Exception $e) {
            Log::error('Error showing IP logs: ' . $e->getMessage());
            return redirect()->route('admin.login-logs.index')
                ->with('error', 'حدث خطأ أثناء عرض سجلات IP: ' . $e->getMessage());
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        try {
            $log = LoginLog::findOrFail($id);
            $log->delete();

            return redirect()->route('admin.login-logs.index')
                ->with('success', 'تم حذف سجل الدخول بنجاح');
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            return redirect()->route('admin.login-logs.index')
                ->with('error', 'سجل الدخول المطلوب غير موجود');
        } catch (\Exception $e) {
            Log::error('Error deleting login log: ' . $e->getMessage());
            return redirect()->route('admin.login-logs.index')
                ->with('error', 'حدث خطأ أثناء حذف سجل الدخول: ' . $e->getMessage());
        }
    }

    /**
     * حذف جميع السجلات القديمة
     */
    public function clearOld(Request $request)
    {
        $request->validate([
            'days' => 'required|integer|min:1|max:365',
        ]);

        try {
            $days = $request->input('days');
            $cutoffDate = now()->subDays($days);

            $deleted = LoginLog::where('login_at', '<', $cutoffDate)->delete();

            return redirect()->route('admin.login-logs.index')
                ->with('success', "تم حذف {$deleted} سجل دخول أقدم من {$days} يوم");
        } catch (\Exception $e) {
            Log::error('Error clearing old logs: ' . $e->getMessage());
            return redirect()->route('admin.login-logs.index')
                ->with('error', 'حدث خطأ أثناء حذف السجلات القديمة: ' . $e->getMessage());
        }
    }
}