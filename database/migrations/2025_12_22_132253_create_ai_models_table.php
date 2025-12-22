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
        Schema::create('ai_models', function (Blueprint $table) {
            $table->id();
            $table->string('name')->comment('اسم الموديل (مثل: GPT-4, Claude, Gemini)');
            $table->string('provider')->comment('المزود: openai, anthropic, google, local, custom');
            $table->string('model_key')->comment('معرف الموديل (gpt-4, claude-3-opus, gemini-pro)');
            $table->text('api_key')->nullable()->comment('مفتاح API (سيتم تشفيره)');
            $table->string('api_endpoint')->nullable()->comment('endpoint مخصص للموديلات المحلية');
            $table->string('base_url')->nullable()->comment('URL أساسي للموديلات المحلية');
            $table->integer('max_tokens')->default(2000);
            $table->decimal('temperature', 3, 2)->default(0.70);
            $table->boolean('is_active')->default(true);
            $table->boolean('is_default')->default(false)->comment('الموديل الافتراضي');
            $table->integer('priority')->default(0)->comment('الأولوية عند التبديل');
            $table->decimal('cost_per_1k_tokens', 10, 6)->nullable()->comment('التكلفة لكل 1000 token');
            $table->json('capabilities')->nullable()->comment('قدرات الموديل: ["chat", "question_generation", "question_solving", "all"]');
            $table->json('settings')->nullable()->comment('إعدادات إضافية');
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
            $table->softDeletes();

            // Indexes
            $table->index('provider');
            $table->index('is_active');
            $table->index('is_default');
            $table->index('priority');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('ai_models');
    }
};
