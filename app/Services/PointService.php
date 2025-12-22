<?php

namespace App\Services;

use App\Models\PointTransaction;
use App\Models\User;
use App\Models\SystemSetting;
use Illuminate\Support\Facades\DB;

class PointService
{
    /**
     * حساب النقاط حسب النوع
     */
    public function calculatePoints(string $type, $metadata = []): int
    {
        // جلب القواعد من الإعدادات
        $setting = SystemSetting::where('key', "gamification_points_{$type}")->first();
        $points = $setting ? (int)$setting->value : $this->getDefaultPoints($type);
        
        // إذا كان هناك metadata، يمكن حساب نقاط إضافية بناءً عليه
        if (!empty($metadata)) {
            $bonus = $this->calculateBonus($type, $metadata);
            return $points + $bonus;
        }

        return $points;
    }

    /**
     * النقاط الافتراضية
     */
    private function getDefaultPoints(string $type): int
    {
        return match($type) {
            'attendance', 'lesson_attended' => 10,
            'lesson_completion', 'lesson_completed' => 15,
            'quiz', 'quiz_completed' => 25,
            'question', 'question_answered' => 5,
            'achievement' => 100,
            'challenge' => 200,
            'task_completion' => 0, // سيتم تحديده من المهمة نفسها
            'reward' => 0,
            'manual' => 0,
            // أحداث المكتبة
            'library_item_viewed' => 2,
            'library_item_downloaded' => 5,
            'library_item_rated' => 3,
            default => 0,
        };
    }

    /**
     * حساب نقاط إضافية
     */
    private function calculateBonus(string $type, $metadata): int
    {
        $bonus = 0;

        if (is_array($metadata)) {
            switch ($type) {
                case 'quiz_completed':
                    if (isset($metadata['percentage']) && $metadata['percentage'] >= 100) {
                        $bonus = SystemSetting::get('gamification_points_quiz_perfect_score', 50);
                    }
                    break;
                case 'question_answered':
                    if (isset($metadata['is_correct']) && $metadata['is_correct']) {
                        $bonus = 2; // نقاط إضافية للإجابة الصحيحة
                    }
                    break;
            }
        }

        return $bonus;
    }

    /**
     * منح النقاط للمستخدم
     */
    public function awardPoints(User $user, string $type, int $points, $source = null, array $metadata = []): PointTransaction
    {
        return DB::transaction(function() use ($user, $type, $points, $source, $metadata) {
            $transaction = PointTransaction::create([
                'user_id' => $user->id,
                'type' => $type,
                'points' => $points,
                'source_type' => $source ? get_class($source) : null,
                'source_id' => $source ? $source->id : null,
                'metadata' => $metadata,
            ]);

            // تحديث إجمالي النقاط في UserLevel
            $this->updateUserTotalPoints($user);

            return $transaction;
        });
    }

    /**
     * إجمالي نقاط المستخدم
     */
    public function getUserTotalPoints(User $user): int
    {
        return PointTransaction::where('user_id', $user->id)
            ->sum('points');
    }

    /**
     * تاريخ النقاط
     */
    public function getUserPointsHistory(User $user, int $limit = 50)
    {
        return PointTransaction::where('user_id', $user->id)
            ->orderBy('created_at', 'desc')
            ->limit($limit)
            ->get();
    }

    /**
     * النقاط حسب النوع
     */
    public function getPointsByType(User $user, string $type): int
    {
        return PointTransaction::where('user_id', $user->id)
            ->where('type', $type)
            ->sum('points');
    }

    /**
     * تحديث إجمالي النقاط
     */
    private function updateUserTotalPoints(User $user): void
    {
        $totalPoints = $this->getUserTotalPoints($user);
        
        $userLevel = $user->userLevel;
        if ($userLevel) {
            $userLevel->total_points_earned = $totalPoints;
            $userLevel->current_points = $totalPoints;
            $userLevel->save();
        }
    }
}

