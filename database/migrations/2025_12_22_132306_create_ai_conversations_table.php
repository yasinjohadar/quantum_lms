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
        Schema::create('ai_conversations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users')->onDelete('cascade');
            $table->foreignId('subject_id')->nullable()->constrained('subjects')->onDelete('set null')->comment('محادثة خاصة بمادة');
            $table->foreignId('lesson_id')->nullable()->constrained('lessons')->onDelete('set null')->comment('محادثة خاصة بدرس');
            $table->enum('conversation_type', ['general', 'subject', 'lesson'])->default('general')->comment('نوع المحادثة');
            $table->string('title')->nullable()->comment('عنوان المحادثة');
            $table->foreignId('ai_model_id')->nullable()->constrained('ai_models')->nullOnDelete()->comment('الموديل المستخدم');
            $table->integer('message_count')->default(0);
            $table->dateTime('last_message_at')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();
            $table->softDeletes();

            // Indexes
            $table->index('user_id');
            $table->index('subject_id');
            $table->index('lesson_id');
            $table->index('conversation_type');
            $table->index('ai_model_id');
            $table->index('is_active');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('ai_conversations');
    }
};
