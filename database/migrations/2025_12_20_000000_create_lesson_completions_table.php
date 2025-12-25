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
            
            // حالة الإكمال
            $table->enum('status', ['attended', 'completed'])->default('attended');
            
            // تاريخ التحديد
            $table->timestamp('marked_at')->useCurrent();
            
            $table->timestamps();
            
            // منع التكرار - كل مستخدم يمكنه إكمال كل درس مرة واحدة فقط
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

