<?php

use App\Models\SystemSetting;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        $rolesTable = config('permission.table_names.roles', 'roles');

        if (Schema::hasColumn($rolesTable, 'staff_profile')) {
            DB::table($rolesTable)
                ->whereIn('name', [
                    'teacher',
                    'teacher-content-uploader',
                    'teacher-assistant',
                    'teacher-quiz-followup',
                ])
                ->update(['staff_profile' => 'teacher']);
        }

        foreach ([
            'content_lesson_mandatory_review' => 'إلزام المعلمين بإرسال الدروس للمراجعة قبل النشر',
            'content_quiz_mandatory_review' => 'إلزام المعلمين بإرسال الاختبارات للمراجعة قبل النشر',
        ] as $key => $description) {
            SystemSetting::updateOrCreate(
                ['key' => $key, 'group' => 'general'],
                [
                    'type' => 'boolean',
                    'description' => $description,
                ]
            );
        }
    }

    public function down(): void
    {
        // لا حاجة للتراجع — تصحيح بيانات تشغيلية
    }
};
