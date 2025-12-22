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
        Schema::create('assignment_questions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('assignment_id')->constrained('assignments')->onDelete('cascade');
            $table->text('question_text')->comment('نص السؤال');
            $table->enum('question_type', ['single_choice', 'multiple_choice', 'true_false', 'short_answer'])->comment('نوع السؤال');
            
            // خيارات السؤال (للاختيار من متعدد)
            $table->json('options')->nullable()->comment('خيارات السؤال: ["option1", "option2", ...]');
            
            // الإجابة الصحيحة
            $table->json('correct_answer')->nullable()->comment('الإجابة الصحيحة');
            
            // درجة السؤال
            $table->decimal('points', 8, 2)->default(0)->comment('درجة السؤال');
            
            // ترتيب السؤال
            $table->integer('order')->default(0);
            
            $table->timestamps();
            
            // Indexes
            $table->index(['assignment_id', 'order']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('assignment_questions');
    }
};
