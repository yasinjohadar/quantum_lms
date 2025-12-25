<?php

namespace App\Services;

use App\Models\DashboardWidget;
use App\Models\SystemSetting;
use App\Models\User;
use App\Models\Subject;
use App\Models\Lesson;
use App\Models\Quiz;
use App\Models\Question;
use App\Models\Enrollment;
use App\Models\QuizAttempt;
use App\Models\LoginLog;
use App\Models\UserSession;
use App\Models\SchoolClass;
use App\Models\Stage;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class AdminDashboardService
{
    /**
     * الحصول على بيانات لوحة التحكم
     */
    public function getDashboardData($userId = null)
    {
        $cacheKey = 'dashboard_data_' . ($userId ?? 'global');
        
        return Cache::remember($cacheKey, 300, function() use ($userId) {
            return [
                'stats' => $this->getQuickStats(),
                'widgets' => $this->getWidgets($userId),
                'recent_activities' => $this->getRecentActivities(),
            ];
        });
    }

    /**
     * حفظ إعدادات الودجت
     */
    public function saveWidgetConfig($userId, $widgets)
    {
        try {
            // حذف الودجت المخصصة الحالية للمستخدم
            DashboardWidget::forUser($userId)
                ->whereNotNull('user_id')
                ->delete();

            // إنشاء الودجت الجديدة
            foreach ($widgets as $widget) {
                DashboardWidget::create([
                    'name' => $widget['name'],
                    'type' => $widget['type'],
                    'position' => $widget['position'] ?? null,
                    'config' => $widget['config'] ?? [],
                    'user_id' => $userId,
                    'is_active' => true,
                ]);
            }

            // مسح الـ cache
            Cache::forget('dashboard_data_' . $userId);

            return true;
        } catch (\Exception $e) {
            \Log::error('Error saving dashboard widgets: ' . $e->getMessage());
            return false;
        }
    }

    /**
     * الحصول على إعدادات النظام
     */
    public function getSystemSettings($group = null)
    {
        $query = SystemSetting::query();
        
        if ($group) {
            $query->ofGroup($group);
        }

        return $query->get()->mapWithKeys(function($setting) {
            return [$setting->key => $setting->value];
        });
    }

    /**
     * تحديث إعدادات النظام
     */
    public function updateSystemSettings($settings, $group = 'general')
    {
        foreach ($settings as $key => $value) {
            SystemSetting::set($key, $value, 'string', $group);
        }

        // مسح الـ cache
        Cache::forget('system_settings');

        return true;
    }

    /**
     * الحصول على الإحصائيات السريعة
     */
    public function getQuickStats()
    {
        $cacheKey = 'dashboard_quick_stats';
        
        return Cache::remember($cacheKey, 60, function() {
            // إحصائيات المستخدمين
            $totalStudents = User::students()->count();
            $activeStudents = User::students()->where('is_active', true)->count();
            $totalTeachers = User::whereHas('roles', function($q) {
                $q->where('name', 'teacher');
            })->count();
            $totalAdmins = User::whereHas('roles', function($q) {
                $q->where('name', 'admin');
            })->count();
            
            // إحصائيات المواد والدروس
            $totalSubjects = Subject::active()->count();
            $totalLessons = Lesson::where('is_active', true)->count();
            
            // إحصائيات الاختبارات
            $totalQuizzes = Quiz::where('is_active', true)->where('is_published', true)->count();
            $totalQuestions = Question::where('is_active', true)->count();
            
            // إحصائيات الانضمامات
            $activeEnrollments = Enrollment::where('status', 'active')->count();
            $pendingEnrollments = Enrollment::where('status', 'pending')->count();
            $totalEnrollments = Enrollment::count();
            
            // إحصائيات محاولات الاختبارات
            $totalQuizAttempts = QuizAttempt::count();
            $completedQuizAttempts = QuizAttempt::completed()->count();
            $todayQuizAttempts = QuizAttempt::whereDate('started_at', today())->count();
            $avgQuizScore = QuizAttempt::completed()->avg('percentage') ?? 0;
            
            // إحصائيات سجلات الدخول
            $todayLogins = LoginLog::whereDate('login_at', today())->where('is_successful', true)->count();
            $todayFailedLogins = LoginLog::whereDate('login_at', today())->where('is_successful', false)->count();
            
            // إحصائيات الجلسات
            $activeSessions = UserSession::active()->count();
            $todaySessions = UserSession::whereDate('started_at', today())->count();
            
            // إحصائيات الصفوف والمراحل
            $totalClasses = SchoolClass::count();
            $totalStages = Stage::count();
            
            // حساب النسب والتغييرات (مقارنة مع الأسبوع الماضي)
            $lastWeekStart = Carbon::now()->subWeek()->startOfWeek();
            $lastWeekEnd = Carbon::now()->subWeek()->endOfWeek();
            
            $lastWeekEnrollments = Enrollment::whereBetween('enrolled_at', [$lastWeekStart, $lastWeekEnd])->count();
            $thisWeekEnrollments = Enrollment::whereBetween('enrolled_at', [Carbon::now()->startOfWeek(), Carbon::now()])->count();
            $enrollmentsChange = $lastWeekEnrollments > 0 
                ? round((($thisWeekEnrollments - $lastWeekEnrollments) / $lastWeekEnrollments) * 100, 1)
                : 0;
            
            $lastWeekQuizAttempts = QuizAttempt::whereBetween('started_at', [$lastWeekStart, $lastWeekEnd])->count();
            $thisWeekQuizAttempts = QuizAttempt::whereBetween('started_at', [Carbon::now()->startOfWeek(), Carbon::now()])->count();
            $quizAttemptsChange = $lastWeekQuizAttempts > 0 
                ? round((($thisWeekQuizAttempts - $lastWeekQuizAttempts) / $lastWeekQuizAttempts) * 100, 1)
                : 0;
            
            return [
                // المستخدمين
                'total_students' => $totalStudents,
                'active_students' => $activeStudents,
                'total_teachers' => $totalTeachers,
                'total_admins' => $totalAdmins,
                'total_users' => User::count(),
                
                // المواد والدروس
                'total_subjects' => $totalSubjects,
                'total_lessons' => $totalLessons,
                
                // الاختبارات
                'total_quizzes' => $totalQuizzes,
                'total_questions' => $totalQuestions,
                'total_quiz_attempts' => $totalQuizAttempts,
                'completed_quiz_attempts' => $completedQuizAttempts,
                'today_quiz_attempts' => $todayQuizAttempts,
                'avg_quiz_score' => round($avgQuizScore, 2),
                
                // الانضمامات
                'active_enrollments' => $activeEnrollments,
                'pending_enrollments' => $pendingEnrollments,
                'total_enrollments' => $totalEnrollments,
                'enrollments_change' => $enrollmentsChange,
                'quiz_attempts_change' => $quizAttemptsChange,
                
                // سجلات الدخول
                'today_logins' => $todayLogins,
                'today_failed_logins' => $todayFailedLogins,
                
                // الجلسات
                'active_sessions' => $activeSessions,
                'today_sessions' => $todaySessions,
                
                // الصفوف والمراحل
                'total_classes' => $totalClasses,
                'total_stages' => $totalStages,
            ];
        });
    }

    /**
     * الحصول على الودجت
     */
    public function getWidgets($userId = null)
    {
        try {
            $query = DashboardWidget::active();
            
            if ($userId) {
                $query->forUser($userId);
            } else {
                $query->global();
            }

            // ترتيب حسب الموضع
            $widgets = $query->get();
            
            return $widgets->sortBy(function($widget) {
                $position = $widget->position ?? ['row' => 0, 'col' => 0];
                return ($position['row'] ?? 0) * 1000 + ($position['col'] ?? 0);
            })->values();
        } catch (\Exception $e) {
            // في حالة عدم وجود الجدول أو أي خطأ آخر، إرجاع مصفوفة فارغة
            \Log::warning('Error fetching dashboard widgets: ' . $e->getMessage());
            return collect([]);
        }
    }

    /**
     * الحصول على الأنشطة الأخيرة
     */
    public function getRecentActivities()
    {
        $cacheKey = 'dashboard_recent_activities';
        
        return Cache::remember($cacheKey, 120, function() {
            try {
                $activities = [];
                
                // آخر الانضمامات
                $recentEnrollments = Enrollment::with(['user', 'subject', 'enrolledBy'])
                    ->latest('enrolled_at')
                    ->limit(5)
                    ->get();
                
                foreach ($recentEnrollments as $enrollment) {
                    if ($enrollment->user && $enrollment->subject) {
                        $activities[] = [
                            'type' => 'enrollment',
                            'icon' => 'user-plus',
                            'color' => 'primary',
                            'title' => 'انضمام جديد',
                            'description' => $enrollment->user->name . ' انضم إلى ' . $enrollment->subject->name,
                            'time' => $enrollment->enrolled_at,
                            'url' => route('admin.enrollments.index'),
                        ];
                    }
                }
                
                // آخر محاولات الاختبارات
                $recentQuizAttempts = QuizAttempt::with(['user', 'quiz'])
                    ->latest('started_at')
                    ->limit(5)
                    ->get();
                
                foreach ($recentQuizAttempts as $attempt) {
                    if ($attempt->user && $attempt->quiz) {
                        $activities[] = [
                            'type' => 'quiz_attempt',
                            'icon' => 'file-text',
                            'color' => $attempt->passed ? 'success' : 'danger',
                            'title' => 'محاولة اختبار جديدة',
                            'description' => $attempt->user->name . ' أكمل ' . $attempt->quiz->title . ' (' . round($attempt->percentage, 1) . '%)',
                            'time' => $attempt->started_at,
                            'url' => route('admin.quiz-attempts.show', $attempt->id),
                        ];
                    }
                }
                
                // آخر المستخدمين المسجلين
                $recentUsers = User::latest('created_at')
                    ->limit(3)
                    ->get();
                
                foreach ($recentUsers as $user) {
                    $activities[] = [
                        'type' => 'user_created',
                        'icon' => 'user',
                        'color' => 'info',
                        'title' => 'مستخدم جديد',
                        'description' => 'تم إنشاء حساب ' . $user->name,
                        'time' => $user->created_at,
                        'url' => route('users.show', $user->id),
                    ];
                }
                
                // ترتيب حسب الوقت (الأحدث أولاً)
                usort($activities, function($a, $b) {
                    return $b['time'] <=> $a['time'];
                });
                
                return array_slice($activities, 0, 10);
            } catch (\Exception $e) {
                // في حالة حدوث خطأ، إرجاع مصفوفة فارغة
                \Log::error('Error fetching recent activities: ' . $e->getMessage());
                return [];
            }
        });
    }
}
