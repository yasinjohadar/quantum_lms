<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * نسبة النجاح للتجربة التفاعلية + علم النجاح للمحاولة.
 *
 * قبل ذلك كانت 50% مكتوبة يدوياً في أربعة مواضع (قوالب العرض وفلتر النتائج)،
 * ولم يكن للمحاولة أي عمود يوازي quiz_attempts.passed فتُحتسب الإحصائيات.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('learning_experiences', function (Blueprint $table) {
            $table->decimal('passing_score', 5, 2)->default(50)->after('engine_version');
        });

        Schema::table('learning_experience_attempts', function (Blueprint $table) {
            $table->boolean('passed')->default(false)->after('percentage')->index();
        });
    }

    public function down(): void
    {
        Schema::table('learning_experiences', function (Blueprint $table) {
            $table->dropColumn('passing_score');
        });

        Schema::table('learning_experience_attempts', function (Blueprint $table) {
            $table->dropIndex(['passed']);
            $table->dropColumn('passed');
        });
    }
};
