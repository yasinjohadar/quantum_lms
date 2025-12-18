<?php

namespace Database\Seeders;

use App\Models\SchoolClass;
use App\Models\Stage;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class ClassSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        if (Stage::count() === 0) {
            $this->command?->warn('لا توجد مراحل مدرسية. يرجى تشغيل StageSeeder أولاً.');
            return;
        }

        $stages = Stage::all();

        foreach ($stages as $stage) {
            $classNames = [
                'الصف الأول',
                'الصف الثاني',
                'الصف الثالث',
                'الصف الرابع',
                'الصف الخامس',
                'الصف السادس',
            ];

            foreach ($classNames as $index => $name) {
                SchoolClass::updateOrCreate(
                    [
                        'name' => $name,
                        'stage_id' => $stage->id,
                    ],
                    [
                        'slug' => Str::slug($name . '-' . $stage->slug),
                        'description' => "صف {$name} في {$stage->name}",
                        'meta_title' => "{$name} - {$stage->name}",
                        'meta_description' => "تعرف على {$name} في {$stage->name}",
                        'meta_keywords' => "{$name}, {$stage->name}, تعليم, منصة تعليمية",
                        'order' => $index,
                        'is_active' => true,
                    ]
                );
            }
        }

        $this->command?->info('تم إنشاء الصفوف الدراسية بنجاح.');
    }
}
