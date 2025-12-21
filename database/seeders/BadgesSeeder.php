<?php

namespace Database\Seeders;

use App\Models\Badge;
use Illuminate\Database\Seeder;

class BadgesSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $badges = [
            [
                'name' => 'المبتدئ',
                'description' => 'شارة للمبتدئين - أول خطوة في رحلة التعلم',
                'icon' => 'fe fe-star',
                'color' => '#28a745',
                'points_required' => 0,
                'criteria' => ['type' => 'automatic', 'points' => 0],
                'is_active' => true,
                'is_automatic' => true,
                'order' => 1,
            ],
            [
                'name' => 'حضور ممتاز',
                'description' => 'حضور 5 دروس متتالية',
                'icon' => 'fe fe-calendar',
                'color' => '#007bff',
                'points_required' => 50,
                'criteria' => ['type' => 'attendance', 'count' => 5, 'consecutive' => true],
                'is_active' => true,
                'is_automatic' => true,
                'order' => 2,
            ],
            [
                'name' => 'مثابر',
                'description' => 'حضور 10 دروس',
                'icon' => 'fe fe-check-circle',
                'color' => '#17a2b8',
                'points_required' => 100,
                'criteria' => ['type' => 'attendance', 'count' => 10],
                'is_active' => true,
                'is_automatic' => true,
                'order' => 3,
            ],
            [
                'name' => 'مكمل',
                'description' => 'إكمال 5 دروس',
                'icon' => 'fe fe-check-square',
                'color' => '#6f42c1',
                'points_required' => 75,
                'criteria' => ['type' => 'lesson_completion', 'count' => 5],
                'is_active' => true,
                'is_automatic' => true,
                'order' => 4,
            ],
            [
                'name' => 'متفوق',
                'description' => 'الحصول على 90% أو أكثر في اختبار',
                'icon' => 'fe fe-award',
                'color' => '#ffc107',
                'points_required' => 150,
                'criteria' => ['type' => 'quiz_score', 'percentage' => 90],
                'is_active' => true,
                'is_automatic' => true,
                'order' => 5,
            ],
            [
                'name' => 'خبير',
                'description' => 'الحصول على 100% في اختبار',
                'icon' => 'fe fe-zap',
                'color' => '#dc3545',
                'points_required' => 200,
                'criteria' => ['type' => 'quiz_score', 'percentage' => 100],
                'is_active' => true,
                'is_automatic' => true,
                'order' => 6,
            ],
            [
                'name' => 'محب للأسئلة',
                'description' => 'الإجابة على 20 سؤال',
                'icon' => 'fe fe-help-circle',
                'color' => '#20c997',
                'points_required' => 120,
                'criteria' => ['type' => 'questions_answered', 'count' => 20],
                'is_active' => true,
                'is_automatic' => true,
                'order' => 7,
            ],
            [
                'name' => 'نشط',
                'description' => 'الحصول على 500 نقطة',
                'icon' => 'fe fe-activity',
                'color' => '#fd7e14',
                'points_required' => 500,
                'criteria' => ['type' => 'points', 'total' => 500],
                'is_active' => true,
                'is_automatic' => true,
                'order' => 8,
            ],
            [
                'name' => 'متميز',
                'description' => 'الحصول على 1000 نقطة',
                'icon' => 'fe fe-trophy',
                'color' => '#e83e8c',
                'points_required' => 1000,
                'criteria' => ['type' => 'points', 'total' => 1000],
                'is_active' => true,
                'is_automatic' => true,
                'order' => 9,
            ],
            [
                'name' => 'أسطورة',
                'description' => 'الحصول على 5000 نقطة',
                'icon' => 'fe fe-crown',
                'color' => '#6c757d',
                'points_required' => 5000,
                'criteria' => ['type' => 'points', 'total' => 5000],
                'is_active' => true,
                'is_automatic' => true,
                'order' => 10,
            ],
            [
                'name' => 'مثالي',
                'description' => 'إكمال كورس كامل',
                'icon' => 'fe fe-book',
                'color' => '#6610f2',
                'points_required' => 300,
                'criteria' => ['type' => 'course_completion', 'percentage' => 100],
                'is_active' => true,
                'is_automatic' => true,
                'order' => 11,
            ],
            [
                'name' => 'سلسلة النجاح',
                'description' => 'حضور 7 أيام متتالية',
                'icon' => 'fe fe-repeat',
                'color' => '#0d6efd',
                'points_required' => 250,
                'criteria' => ['type' => 'streak', 'days' => 7],
                'is_active' => true,
                'is_automatic' => true,
                'order' => 12,
            ],
        ];

        foreach ($badges as $badge) {
            Badge::create($badge);
        }
    }
}

