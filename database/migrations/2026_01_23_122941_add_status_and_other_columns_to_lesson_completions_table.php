<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('lesson_completions', function (Blueprint $table) {
            // إضافة عمود status إذا لم يكن موجوداً
            if (!Schema::hasColumn('lesson_completions', 'status')) {
                $table->string('status', 20)->nullable()->after('lesson_id')
                    ->comment('حالة الإكمال: attended (حضر) أو completed (أكمل)');
            }
            
            // إضافة عمود marked_at إذا لم يكن موجوداً
            if (!Schema::hasColumn('lesson_completions', 'marked_at')) {
                $table->timestamp('marked_at')->nullable()->after('status')
                    ->comment('تاريخ ووقت تحديد الحالة');
            }
            
            // إضافة عمود last_position إذا لم يكن موجوداً
            if (!Schema::hasColumn('lesson_completions', 'last_position')) {
                $table->integer('last_position')->nullable()->after('marked_at')
                    ->comment('الموضع الأخير في الفيديو بالثواني');
            }
            
            // إضافة عمود time_spent إذا لم يكن موجوداً (مختلف عن time_spent_seconds)
            if (!Schema::hasColumn('lesson_completions', 'time_spent')) {
                $table->integer('time_spent')->nullable()->after('last_position')
                    ->comment('الوقت المستغرق في مشاهدة الدرس بالثواني');
            }
        });
        
        // جعل عمود completed_at nullable إذا كان موجوداً (لأن الكود يستخدم marked_at الآن)
        // نستخدم DB::statement مباشرة لتجنب مشاكل doctrine/dbal
        if (Schema::hasColumn('lesson_completions', 'completed_at')) {
            DB::statement('ALTER TABLE `lesson_completions` MODIFY COLUMN `completed_at` TIMESTAMP NULL');
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('lesson_completions', function (Blueprint $table) {
            if (Schema::hasColumn('lesson_completions', 'status')) {
                $table->dropColumn('status');
            }
            if (Schema::hasColumn('lesson_completions', 'marked_at')) {
                $table->dropColumn('marked_at');
            }
            if (Schema::hasColumn('lesson_completions', 'last_position')) {
                $table->dropColumn('last_position');
            }
            if (Schema::hasColumn('lesson_completions', 'time_spent')) {
                $table->dropColumn('time_spent');
            }
        });
    }
};
