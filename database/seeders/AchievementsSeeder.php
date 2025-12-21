<?php

namespace Database\Seeders;

use App\Models\Achievement;
use App\Models\Badge;
use Illuminate\Database\Seeder;

class AchievementsSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $badgeIds = Badge::pluck('id')->toArray();

        $achievements = [
            [
                'name' => 'أول خطوة',
                'description' => 'حضور أول درس',
                'type' => 'attendance',
                'criteria' => ['type' => 'attendance', 'count' => 1],
                'points_reward' => 10,
                'badge_id' => $badgeIds[0] ?? null,
                'is_active' => true,
                'order' => 1,
            ],
            [
                'name' => 'حضور منتظم',
                'description' => 'حضور 5 دروس',
                'type' => 'attendance',
                'criteria' => ['type' => 'attendance', 'count' => 5],
                'points_reward' => 50,
                'badge_id' => $badgeIds[1] ?? null,
                'is_active' => true,
                'order' => 2,
            ],
            [
                'name' => 'مثابر',
                'description' => 'حضور 10 دروس',
                'type' => 'attendance',
                'criteria' => ['type' => 'attendance', 'count' => 10],
                'points_reward' => 100,
                'badge_id' => $badgeIds[2] ?? null,
                'is_active' => true,
                'order' => 3,
            ],
            [
                'name' => 'أول اختبار',
                'description' => 'إكمال أول اختبار',
                'type' => 'quiz',
                'criteria' => ['type' => 'quiz_completed', 'count' => 1],
                'points_reward' => 25,
                'badge_id' => null,
                'is_active' => true,
                'order' => 4,
            ],
            [
                'name' => 'خبير الاختبارات',
                'description' => 'إكمال 5 اختبارات',
                'type' => 'quiz',
                'criteria' => ['type' => 'quiz_completed', 'count' => 5],
                'points_reward' => 150,
                'badge_id' => null,
                'is_active' => true,
                'order' => 5,
            ],
            [
                'name' => 'متفوق',
                'description' => 'الحصول على 90% في اختبار',
                'type' => 'quiz',
                'criteria' => ['type' => 'quiz_score', 'percentage' => 90],
                'points_reward' => 200,
                'badge_id' => $badgeIds[4] ?? null,
                'is_active' => true,
                'order' => 6,
            ],
            [
                'name' => 'إكمال كورس',
                'description' => 'إكمال كورس كامل',
                'type' => 'course',
                'criteria' => ['type' => 'course_completion', 'percentage' => 100],
                'points_reward' => 300,
                'badge_id' => $badgeIds[10] ?? null,
                'is_active' => true,
                'order' => 7,
            ],
            [
                'name' => 'سلسلة النجاح',
                'description' => 'حضور 7 أيام متتالية',
                'type' => 'streak',
                'criteria' => ['type' => 'attendance_streak', 'days' => 7],
                'points_reward' => 250,
                'badge_id' => $badgeIds[11] ?? null,
                'is_active' => true,
                'order' => 8,
            ],
            [
                'name' => 'إنجاز خاص',
                'description' => 'إنجاز خاص من الأدمن',
                'type' => 'special',
                'criteria' => ['type' => 'manual'],
                'points_reward' => 500,
                'badge_id' => null,
                'is_active' => true,
                'order' => 9,
            ],
            [
                'name' => 'مثالي',
                'description' => 'الحصول على 100% في 3 اختبارات',
                'type' => 'quiz',
                'criteria' => ['type' => 'perfect_scores', 'count' => 3],
                'points_reward' => 400,
                'badge_id' => $badgeIds[5] ?? null,
                'is_active' => true,
                'order' => 10,
            ],
        ];

        foreach ($achievements as $achievement) {
            Achievement::create($achievement);
        }
    }
}

