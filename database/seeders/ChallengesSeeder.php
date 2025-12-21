<?php

namespace Database\Seeders;

use App\Models\Challenge;
use Illuminate\Database\Seeder;
use Carbon\Carbon;

class ChallengesSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $challenges = [
            [
                'name' => 'تحدي الأسبوع الأول',
                'description' => 'حضور 5 دروس خلال هذا الأسبوع',
                'type' => 'weekly',
                'start_date' => Carbon::now()->startOfWeek(),
                'end_date' => Carbon::now()->endOfWeek(),
                'criteria' => ['type' => 'attendance', 'count' => 5, 'period' => 'week'],
                'rewards' => ['points' => 100, 'badge_id' => null],
                'is_active' => true,
            ],
            [
                'name' => 'تحدي الشهر',
                'description' => 'إكمال 10 دروس خلال هذا الشهر',
                'type' => 'monthly',
                'start_date' => Carbon::now()->startOfMonth(),
                'end_date' => Carbon::now()->endOfMonth(),
                'criteria' => ['type' => 'lesson_completion', 'count' => 10, 'period' => 'month'],
                'rewards' => ['points' => 300, 'badge_id' => null],
                'is_active' => true,
            ],
            [
                'name' => 'تحدي الاختبارات',
                'description' => 'إكمال 3 اختبارات خلال هذا الأسبوع',
                'type' => 'weekly',
                'start_date' => Carbon::now()->startOfWeek(),
                'end_date' => Carbon::now()->endOfWeek(),
                'criteria' => ['type' => 'quiz_completed', 'count' => 3, 'period' => 'week'],
                'rewards' => ['points' => 150, 'badge_id' => null],
                'is_active' => true,
            ],
            [
                'name' => 'تحدي المثابرة',
                'description' => 'حضور 7 أيام متتالية',
                'type' => 'custom',
                'start_date' => Carbon::now(),
                'end_date' => Carbon::now()->addDays(7),
                'criteria' => ['type' => 'attendance_streak', 'days' => 7],
                'rewards' => ['points' => 250, 'badge_id' => null],
                'is_active' => true,
            ],
            [
                'name' => 'تحدي التفوق',
                'description' => 'الحصول على 90% أو أكثر في 3 اختبارات',
                'type' => 'monthly',
                'start_date' => Carbon::now()->startOfMonth(),
                'end_date' => Carbon::now()->endOfMonth(),
                'criteria' => ['type' => 'quiz_score', 'percentage' => 90, 'count' => 3, 'period' => 'month'],
                'rewards' => ['points' => 400, 'badge_id' => null],
                'is_active' => true,
            ],
        ];

        foreach ($challenges as $challenge) {
            Challenge::create($challenge);
        }
    }
}

