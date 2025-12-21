<?php

namespace App\Http\Controllers\Student;

use App\Http\Controllers\Controller;
use App\Services\PointService;
use App\Services\BadgeService;
use App\Services\AchievementService;
use App\Services\LevelService;
use App\Services\ChallengeService;
use App\Services\RewardService;
use App\Services\CertificateService;
use App\Services\LeaderboardService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class GamificationController extends Controller
{
    public function __construct(
        private PointService $pointService,
        private BadgeService $badgeService,
        private AchievementService $achievementService,
        private LevelService $levelService,
        private ChallengeService $challengeService,
        private RewardService $rewardService,
        private CertificateService $certificateService,
        private LeaderboardService $leaderboardService
    ) {}

    /**
     * لوحة التحفيز الرئيسية
     */
    public function dashboard()
    {
        $user = Auth::user();

        $stats = [
            'total_points' => $this->pointService->getUserTotalPoints($user),
            'badges_count' => $user->badges()->count(),
            'achievements_count' => $user->achievements()->wherePivot('completed_at', '!=', null)->count(),
            'current_level' => $this->levelService->getUserLevel($user),
            'level_progress' => $this->levelService->getLevelProgress($user),
        ];

        $recentBadges = $user->badges()->orderByPivot('earned_at', 'desc')->limit(5)->get();
        $recentAchievements = $user->achievements()
            ->wherePivot('completed_at', '!=', null)
            ->orderByPivot('completed_at', 'desc')
            ->limit(5)
            ->get();

        return view('student.pages.gamification.dashboard', [
            'stats' => $stats,
            'recentBadges' => $recentBadges,
            'recentAchievements' => $recentAchievements
        ]);
    }

    /**
     * صفحة الشارات
     */
    public function badges()
    {
        $user = Auth::user();
        $userBadges = $this->badgeService->getUserBadges($user);
        $availableBadges = $this->badgeService->getAvailableBadges($user);

        return view('student.pages.gamification.badges', [
            'userBadges' => $userBadges,
            'availableBadges' => $availableBadges
        ]);
    }

    /**
     * صفحة الإنجازات
     */
    public function achievements()
    {
        $user = Auth::user();
        $achievements = $this->achievementService->getUserAchievements($user);

        return view('student.pages.gamification.achievements', compact('achievements'));
    }

    /**
     * لوحة المتصدرين
     */
    public function leaderboard(Request $request)
    {
        $leaderboardId = $request->get('leaderboard_id');
        
        if ($leaderboardId) {
            $leaderboard = \App\Models\Leaderboard::findOrFail($leaderboardId);
            $entries = $this->leaderboardService->getLeaderboard($leaderboard);
            $userRank = $this->leaderboardService->getUserRank($leaderboard, Auth::user());
        } else {
            // لوحة المتصدرين العامة
            $leaderboard = \App\Models\Leaderboard::where('type', 'global')
                ->active()
                ->first();
            
            if ($leaderboard) {
                $entries = $this->leaderboardService->getLeaderboard($leaderboard);
                $userRank = $this->leaderboardService->getUserRank($leaderboard, Auth::user());
            } else {
                $entries = collect();
                $userRank = null;
            }
        }

        $allLeaderboards = \App\Models\Leaderboard::active()->get();

        return view('student.pages.gamification.leaderboard', compact('leaderboard', 'entries', 'userRank', 'allLeaderboards'));
    }

    /**
     * التحديات
     */
    public function challenges()
    {
        $user = Auth::user();
        $activeChallenges = $this->challengeService->getActiveChallenges($user);
        $userChallenges = $this->challengeService->getUserChallenges($user);

        return view('student.pages.gamification.challenges', [
            'activeChallenges' => $activeChallenges,
            'userChallenges' => $userChallenges
        ]);
    }

    /**
     * متجر المكافآت
     */
    public function rewards()
    {
        $user = Auth::user();
        $availableRewards = $this->rewardService->getAvailableRewards();
        $userRewards = $this->rewardService->getUserRewards($user);
        $totalPoints = $this->pointService->getUserTotalPoints($user);

        return view('student.pages.gamification.rewards', [
            'availableRewards' => $availableRewards,
            'userRewards' => $userRewards,
            'totalPoints' => $totalPoints
        ]);
    }

    /**
     * استبدال مكافأة
     */
    public function claimReward(Request $request, $rewardId)
    {
        $user = Auth::user();
        $reward = \App\Models\Reward::findOrFail($rewardId);

        try {
            $this->rewardService->claimReward($user, $reward);
            return redirect()->back()->with('success', 'تم استبدال المكافأة بنجاح');
        } catch (\Exception $e) {
            return redirect()->back()->with('error', $e->getMessage());
        }
    }

    /**
     * الشهادات
     */
    public function certificates()
    {
        $user = Auth::user();
        $certificates = $user->certificates()->with('subject')->orderBy('issued_at', 'desc')->get();

        return view('student.pages.gamification.certificates', [
            'certificates' => $certificates
        ]);
    }

    /**
     * تحميل شهادة
     */
    public function downloadCertificate($certificateId)
    {
        $user = Auth::user();
        $certificate = $user->certificates()->findOrFail($certificateId);

        return $this->certificateService->downloadCertificate($certificate);
    }

    /**
     * الإحصائيات الشخصية
     */
    public function stats()
    {
        $user = Auth::user();

        $stats = [
            'total_points' => $this->pointService->getUserTotalPoints($user),
            'points_by_type' => [
                'attendance' => $this->pointService->getPointsByType($user, 'attendance'),
                'lesson_completion' => $this->pointService->getPointsByType($user, 'lesson_completion'),
                'quiz' => $this->pointService->getPointsByType($user, 'quiz'),
                'question' => $this->pointService->getPointsByType($user, 'question'),
                'achievement' => $this->pointService->getPointsByType($user, 'achievement'),
            ],
            'level_progress' => $this->levelService->getLevelProgress($user),
            'points_history' => $this->pointService->getUserPointsHistory($user, 30),
        ];

        return view('student.pages.gamification.stats', [
            'stats' => $stats
        ]);
    }
}

