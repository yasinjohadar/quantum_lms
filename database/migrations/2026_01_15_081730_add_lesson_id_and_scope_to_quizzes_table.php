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
        Schema::table('quizzes', function (Blueprint $table) {
            // حقل لاختبار الدرس (فيديو معيّن داخل الوحدة)
            if (!Schema::hasColumn('quizzes', 'lesson_id')) {
                $table->foreignId('lesson_id')
                    ->nullable()
                    ->after('unit_id')
                    ->constrained('lessons')
                    ->nullOnDelete()
                    ->comment('اختياري - إذا كان الاختبار مرتبطاً بدرس معيّن داخل الوحدة');
            }

            // حقل لتحديد نوع التبعية (للوحدة كاملة أو لدرس واحد)
            if (!Schema::hasColumn('quizzes', 'scope')) {
                $table->enum('scope', ['unit', 'lesson'])
                    ->default('unit')
                    ->after('lesson_id')
                    ->comment('نوع التبعية: unit لاختبار الوحدة، lesson لاختبار الدرس');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('quizzes', function (Blueprint $table) {
            if (Schema::hasColumn('quizzes', 'lesson_id')) {
                $table->dropForeign(['lesson_id']);
                $table->dropColumn('lesson_id');
            }

            if (Schema::hasColumn('quizzes', 'scope')) {
                $table->dropColumn('scope');
            }
        });
    }
};
