<?php

use App\Models\SystemSetting;
use Illuminate\Database\Migrations\Migration;

return new class extends Migration
{
    public function up(): void
    {
        if (! SystemSetting::where('key', 'content_lesson_mandatory_review')->where('group', 'general')->exists()) {
            SystemSetting::set(
                'content_lesson_mandatory_review',
                '0',
                'boolean',
                'general',
                'إلزام المعلمين بإرسال الدروس للمراجعة قبل النشر'
            );
        }
    }

    public function down(): void
    {
        SystemSetting::where('key', 'content_lesson_mandatory_review')
            ->where('group', 'general')
            ->delete();
    }
};
