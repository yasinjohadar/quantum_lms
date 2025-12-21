<?php

namespace App\Services;

use App\Models\Leaderboard;
use App\Models\LeaderboardEntry;
use App\Models\User;
use App\Services\PointService;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;

class LeaderboardService
{
    public function __construct(
        private PointService $pointService
    ) {}

    /**
     * تحديث اللوحة
     */
    public function updateLeaderboard(Leaderboard $leaderboard): void
    {
        $criteria = $leaderboard->criteria ?? [];
        $type = $leaderboard->type;

        // حذف الإدخالات القديمة
        LeaderboardEntry::where('leaderboard_id', $leaderboard->id)->delete();

        $scores = [];

        switch ($type) {
            case 'global':
                $scores = $this->calculateGlobalScores($criteria);
                break;

            case 'course':
                if ($leaderboard->subject_id) {
                    $scores = $this->calculateCourseScores($leaderboard->subject_id, $criteria);
                }
                break;

            case 'weekly':
            case 'monthly':
                $scores = $this->calculatePeriodScores($leaderboard, $criteria);
                break;
        }

        // إدراج الإدخالات الجديدة
        $rank = 1;
        foreach ($scores as $userId => $score) {
            LeaderboardEntry::create([
                'leaderboard_id' => $leaderboard->id,
                'user_id' => $userId,
                'rank' => $rank++,
                'score' => $score,
                'metadata' => [],
            ]);
        }

        // مسح الكاش
        Cache::forget("leaderboard_{$leaderboard->id}");
    }

    /**
     * حساب النقاط العامة
     */
    private function calculateGlobalScores(array $criteria): array
    {
        $scores = [];

        $users = User::students()->get();
        foreach ($users as $user) {
            $score = $this->pointService->getUserTotalPoints($user);
            $scores[$user->id] = $score;
        }

        arsort($scores);
        return $scores;
    }

    /**
     * حساب النقاط حسب الكورس
     */
    private function calculateCourseScores(int $subjectId, array $criteria): array
    {
        $scores = [];

        $users = User::whereHas('subjects', function($q) use ($subjectId) {
            $q->where('subjects.id', $subjectId);
        })->get();

        foreach ($users as $user) {
            // حساب النقاط من الأنشطة المتعلقة بهذا الكورس
            $score = $this->calculateSubjectPoints($user, $subjectId);
            $scores[$user->id] = $score;
        }

        arsort($scores);
        return $scores;
    }

    /**
     * حساب النقاط للفترة
     */
    private function calculatePeriodScores(Leaderboard $leaderboard, array $criteria): array
    {
        $scores = [];
        $startDate = $leaderboard->period_start ?? now()->startOfMonth();
        $endDate = $leaderboard->period_end ?? now()->endOfMonth();

        $users = User::students()->get();
        foreach ($users as $user) {
            $score = PointTransaction::where('user_id', $user->id)
                ->whereBetween('created_at', [$startDate, $endDate])
                ->sum('points');
            $scores[$user->id] = $score;
        }

        arsort($scores);
        return $scores;
    }

    /**
     * حساب نقاط الكورس
     */
    private function calculateSubjectPoints(User $user, int $subjectId): int
    {
        // حساب النقاط من الأنشطة المتعلقة بهذا الكورس
        // يمكن تحسين هذا ليشمل الدروس والاختبارات والأسئلة
        return PointTransaction::where('user_id', $user->id)
            ->whereJsonContains('metadata->subject_id', $subjectId)
            ->sum('points');
    }

    /**
     * جلب اللوحة
     */
    public function getLeaderboard(Leaderboard $leaderboard, int $limit = 100)
    {
        $cacheKey = "leaderboard_{$leaderboard->id}_{$limit}";

        return Cache::remember($cacheKey, 300, function() use ($leaderboard, $limit) {
            return LeaderboardEntry::where('leaderboard_id', $leaderboard->id)
                ->with('user')
                ->orderBy('rank')
                ->limit($limit)
                ->get();
        });
    }

    /**
     * ترتيب المستخدم
     */
    public function getUserRank(Leaderboard $leaderboard, User $user): ?int
    {
        $entry = LeaderboardEntry::where('leaderboard_id', $leaderboard->id)
            ->where('user_id', $user->id)
            ->first();

        return $entry ? $entry->rank : null;
    }
}

