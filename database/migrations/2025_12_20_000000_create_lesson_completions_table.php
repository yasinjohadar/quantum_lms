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
        Schema::create('lesson_completions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users')->onDelete('cascade');
            $table->foreignId('lesson_id')->constrained('lessons')->onDelete('cascade');
            
            // حالة الدرس
            $table->enum('status', ['attended', 'completed'])->default('attended')
                ->comment('attended = تم الحضور، completed = تم الإكمال');
            
            // التوقيت
            $table->timestamp('marked_at')->useCurrent();
            $table->integer('time_spent')->nullable()->comment('الوقت المستغرق في مشاهدة الدرس بالثواني');
            
            // ملاحظات
            $table->text('notes')->nullable();
            
            $table->timestamps();
            $table->softDeletes();
            
            // منع التكرار - طالب واحد يمكنه تحديد حالة واحدة لكل درس
            $table->unique(['user_id', 'lesson_id']);
            
            // فهارس للأداء
            $table->index('user_id');
            $table->index('lesson_id');
            $table->index('status');
            $table->index('marked_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('lesson_completions');
    }
};

