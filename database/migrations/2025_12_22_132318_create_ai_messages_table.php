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
        Schema::create('ai_messages', function (Blueprint $table) {
            $table->id();
            $table->foreignId('conversation_id')->constrained('ai_conversations')->onDelete('cascade');
            $table->enum('role', ['user', 'assistant', 'system'])->default('user');
            $table->text('content');
            $table->integer('tokens_used')->nullable()->comment('عدد الـ tokens المستخدمة');
            $table->decimal('cost', 10, 6)->nullable()->comment('التكلفة');
            $table->integer('response_time')->nullable()->comment('الوقت بالمللي ثانية');
            $table->json('metadata')->nullable()->comment('بيانات إضافية');
            $table->timestamps();

            // Indexes
            $table->index('conversation_id');
            $table->index('role');
            $table->index('created_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('ai_messages');
    }
};
