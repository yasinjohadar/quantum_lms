<?php

namespace Database\Seeders;

use App\Models\DailyTask;
use App\Models\WeeklyTask;
use Illuminate\Database\Seeder;

class TasksSeeder extends Seeder
{
    /**
     * Run the database seeds.
     * مهام يومية وأسبوعية افتراضية لنظام التحفيز.
     */
    public function run(): void
    {
        $dailyTasks = [
            [
                'name' => 'حضور درس واحد اليوم',
                'description' => 'احضر درساً واحداً على الأقل اليوم',
                'type' => 'attendance',
                'points_reward' => 15,
                'criteria' => ['type' => 'attendance', 'count' => 1],
                'is_active' => true,
                'order' => 1,
            ],
            [
                'name' => 'إكمال درس واحد اليوم',
                'description' => 'أكمل درساً واحداً على الأقل اليوم',
                'type' => 'lesson_completion',
                'points_reward' => 20,
                'criteria' => ['type' => 'lesson_completion', 'count' => 1],
                'is_active' => true,
                'order' => 2,
            ],
            [
                'name' => 'إكمال اختبار واحد اليوم',
                'description' => 'أكمل اختباراً واحداً على الأقل اليوم',
                'type' => 'quiz',
                'points_reward' => 30,
                'criteria' => ['type' => 'quiz_completed', 'count' => 1],
                'is_active' => true,
                'order' => 3,
            ],
            [
                'name' => 'الإجابة على 5 أسئلة اليوم',
                'description' => 'أجب على 5 أسئلة على الأقل اليوم',
                'type' => 'question',
                'points_reward' => 25,
                'criteria' => ['type' => 'questions_answered', 'count' => 5],
                'is_active' => true,
                'order' => 4,
            ],
        ];

        foreach ($dailyTasks as $task) {
            DailyTask::updateOrCreate(
                ['name' => $task['name']],
                $task
            );
        }

        $weeklyTasks = [
            [
                'name' => 'حضور 5 دروس هذا الأسبوع',
                'description' => 'احضر 5 دروس على الأقل خلال الأسبوع',
                'type' => 'attendance',
                'points_reward' => 80,
                'criteria' => ['type' => 'attendance', 'count' => 5, 'period' => 'week'],
                'start_day' => 1,
                'end_day' => 7,
                'is_active' => true,
                'order' => 1,
            ],
            [
                'name' => 'إكمال 3 اختبارات هذا الأسبوع',
                'description' => 'أكمل 3 اختبارات على الأقل خلال الأسبوع',
                'type' => 'quiz',
                'points_reward' => 100,
                'criteria' => ['type' => 'quiz_completed', 'count' => 3, 'period' => 'week'],
                'start_day' => 1,
                'end_day' => 7,
                'is_active' => true,
                'order' => 2,
            ],
            [
                'name' => 'إكمال 10 دروس هذا الأسبوع',
                'description' => 'أكمل 10 دروس على الأقل خلال الأسبوع',
                'type' => 'lesson_completion',
                'points_reward' => 150,
                'criteria' => ['type' => 'lesson_completion', 'count' => 10, 'period' => 'week'],
                'start_day' => 1,
                'end_day' => 7,
                'is_active' => true,
                'order' => 3,
            ],
        ];

        foreach ($weeklyTasks as $task) {
            WeeklyTask::updateOrCreate(
                ['name' => $task['name']],
                $task
            );
        }
    }
}
