<?php

namespace Database\Seeders;

use App\Models\SchoolClass;
use App\Models\Subject;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class SubjectSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        if (SchoolClass::count() === 0) {
            $this->command?->warn('لا توجد صفوف مدرسية. يرجى تشغيل ClassSeeder أولاً.');
            return;
        }

        $classes = SchoolClass::with('stage')->get();

        $subjectsByStage = [
            'الابتدائية' => [
                'الرياضيات',
                'العلوم',
                'اللغة العربية',
                'اللغة الإنجليزية',
                'التربية الإسلامية',
                'التربية الفنية',
                'التربية البدنية',
                'الحاسوب',
            ],
            'المتوسطة' => [
                'الرياضيات',
                'العلوم',
                'الفيزياء',
                'الكيمياء',
                'الأحياء',
                'اللغة العربية',
                'اللغة الإنجليزية',
                'التاريخ',
                'الجغرافيا',
                'التربية الإسلامية',
                'الحاسوب',
                'التربية الفنية',
            ],
            'الثانوية' => [
                'الرياضيات',
                'الرياضيات المتقدمة',
                'الفيزياء',
                'الكيمياء',
                'الأحياء',
                'اللغة العربية',
                'اللغة الإنجليزية',
                'التاريخ',
                'الجغرافيا',
                'التربية الإسلامية',
                'الحاسوب',
                'اللغة الفرنسية',
                'الفلسفة',
                'علم النفس',
            ],
        ];

        foreach ($classes as $class) {
            $stageName = $class->stage?->name ?? 'الابتدائية';

            $subjects = $subjectsByStage[$stageName] ?? $subjectsByStage['الابتدائية'];

            if (! isset($subjectsByStage[$stageName])) {
                foreach ($subjectsByStage as $key => $value) {
                    if (str_contains($stageName, $key) || str_contains($key, $stageName)) {
                        $subjects = $value;
                        break;
                    }
                }
            }

            foreach ($subjects as $index => $name) {
                Subject::updateOrCreate(
                    [
                        'name' => $name,
                        'class_id' => $class->id,
                    ],
                    [
                        'slug' => Str::slug($name . '-' . $class->slug),
                        'description' => "مادة {$name} في {$class->name} - {$stageName}",
                        'meta_title' => "{$name} - {$class->name} - {$stageName}",
                        'meta_description' => "تعرف على مادة {$name} في {$class->name} ضمن {$stageName}",
                        'meta_keywords' => "{$name}, {$class->name}, {$stageName}, تعليم, منصة تعليمية",
                        'order' => $index,
                        'is_active' => true,
                        'display_in_class' => true,
                    ]
                );
            }
        }

        $this->command?->info('تم إنشاء المواد الدراسية بنجاح.');
    }
}

