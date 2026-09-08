<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // ملاحظة: audit_logs.subject_id/subject_type يعنيان "نوع ومعرّف العنصر المُدقَّق نفسه"
        // (مثلاً lesson #5) — لذا نستخدم أسماء مختلفة (curriculum_*) لمرجع "الصف" و"المادة"
        // اللذين ينتمي إليهما هذا العنصر، تفادياً لتضارب الأسماء.
        Schema::table('audit_logs', function (Blueprint $table) {
            $table->unsignedBigInteger('class_id')->nullable()->after('subject_label');
            $table->string('class_name')->nullable()->after('class_id');
            $table->unsignedBigInteger('curriculum_subject_id')->nullable()->after('class_name');
            $table->string('curriculum_subject_name')->nullable()->after('curriculum_subject_id');

            $table->index('class_id');
            $table->index('curriculum_subject_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('audit_logs', function (Blueprint $table) {
            $table->dropIndex(['class_id']);
            $table->dropIndex(['curriculum_subject_id']);
            $table->dropColumn(['class_id', 'class_name', 'curriculum_subject_id', 'curriculum_subject_name']);
        });
    }
};
