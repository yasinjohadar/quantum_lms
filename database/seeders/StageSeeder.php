<?php

namespace Database\Seeders;

use App\Models\Stage;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class StageSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $stages = [
            [
                'name' => 'الابتدائية',
                'description' => 'المرحلة الابتدائية - من الصف الأول إلى الصف السادس.',
                'order' => 1,
            ],
            [
                'name' => 'المتوسطة',
                'description' => 'المرحلة المتوسطة - من الصف الأول إلى الصف الثالث متوسط.',
                'order' => 2,
            ],
            [
                'name' => 'الثانوية',
                'description' => 'المرحلة الثانوية - من الصف الأول إلى الصف الثالث ثانوي.',
                'order' => 3,
            ],
        ];

        foreach ($stages as $data) {
            Stage::updateOrCreate(
                ['name' => $data['name']],
                [
                    'slug' => Str::slug($data['name']),
                    'description' => $data['description'],
                    'order' => $data['order'],
                    'is_active' => true,
                ]
            );
        }

        $this->command?->info('تم إنشاء المراحل الدراسية بنجاح.');
    }
}


