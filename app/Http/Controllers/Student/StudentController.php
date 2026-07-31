<?php

namespace App\Http\Controllers\Student;

use App\Http\Controllers\Controller;
use App\Services\StudentProgressService;
use App\Services\PointService;
use App\Services\LevelService;
use App\Services\BadgeService;
use App\Models\UserBadge;
use Illuminate\Support\Facades\Auth;

class StudentController extends Controller
{
    public function __construct(
        private StudentProgressService $progressService,
        private PointService $pointService,
        private LevelService $levelService,
        private BadgeService $badgeService,
    ) {
        $this->middleware('auth');
        $this->middleware('check.user.active');
    }

    /**
     * عرض لوحة تحكم الطالب
     */
    public function dashboard()
    {
        $user = Auth::user();
        
        // التحقق من أن المستخدم لديه صلاحية student
        // إذا لم يكن لديه صلاحية، نعطيه صلاحية student تلقائياً (للمستخدمين القدامى)
        if (!$user->hasRole('student')) {
            // محاولة إعطاء صلاحية student تلقائياً
            try {
                $user->assignRole('student');
            } catch (\Exception $e) {
                // إذا فشل تعيين الصلاحية، نعرض رسالة خطأ
                abort(403, 'ليس لديك صلاحية للوصول إلى هذه الصفحة. يرجى التواصل مع الإدارة.');
            }
        }
        
        // تقدم الطالب في مواده
        $progressList = $this->progressService->getAllStudentProgress($user->id);
        $subjectsProgress = collect($progressList);
        $overallAverage = $subjectsProgress->avg(function ($item) {
            return $item['progress']['overall_percentage'] ?? 0;
        }) ?? 0;
        $topSubjects = $subjectsProgress
            ->sortByDesc(function ($item) {
                return $item['progress']['overall_percentage'] ?? 0;
            })
            ->take(4);

        // إحصائيات Gamification الأساسية
        $totalPoints = $this->pointService->getUserTotalPoints($user);
        $currentLevel = $this->levelService->getUserLevel($user);
        $levelProgress = $this->levelService->getLevelProgress($user);
        $badgesCount = $user->badges()->count();
        $achievementsCount = $user->achievements()
            ->wherePivot('completed_at', '!=', null)
            ->count();

        // آخر الشارات (5 أحدث)
        $latestBadges = UserBadge::where('user_id', $user->id)
            ->with('badge')
            ->latest('earned_at')
            ->limit(5)
            ->get();

        return view('student.dashboard', [
            'user' => $user,
            'overallAverage' => round($overallAverage, 1),
            'subjectsCount' => $subjectsProgress->count(),
            'topSubjects' => $topSubjects,
            'upcomingEvents' => collect(),
            'totalPoints' => $totalPoints,
            'currentLevel' => $currentLevel,
            'levelProgress' => $levelProgress,
            'badgesCount' => $badgesCount,
            'achievementsCount' => $achievementsCount,
            'latestBadges' => $latestBadges,
        ]);
    }
}
