<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('learning_experiences', function (Blueprint $table) {
            $table->id();
            $table->string('title');
            $table->text('description')->nullable();
            $table->string('status', 32)->default('draft')->index();
            $table->json('schema_json');
            $table->string('schema_version', 16)->default('1.0');
            $table->string('engine_version', 16)->default('1.0');
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
        });

        Schema::create('learning_experience_attempts', function (Blueprint $table) {
            $table->id();
            $table->foreignId('learning_experience_id')->constrained('learning_experiences')->cascadeOnDelete();
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
            $table->unsignedInteger('score')->default(0);
            $table->unsignedInteger('total')->default(0);
            $table->decimal('percentage', 5, 2)->default(0);
            $table->unsignedInteger('duration')->default(0);
            $table->timestamp('started_at')->nullable();
            $table->timestamp('finished_at')->nullable();
            $table->json('answers_json')->nullable();
            $table->json('result_json')->nullable();
            $table->timestamps();

            $table->index(['learning_experience_id', 'user_id'], 'le_attempts_exp_user_idx');
        });

        Schema::create('learning_experience_attachables', function (Blueprint $table) {
            $table->id();
            $table->foreignId('learning_experience_id')->constrained('learning_experiences')->cascadeOnDelete();
            $table->string('attachable_type');
            $table->unsignedBigInteger('attachable_id');
            $table->unsignedInteger('order')->default(0);
            $table->timestamps();

            $table->index(['attachable_type', 'attachable_id'], 'le_attachables_morph_idx');
            $table->unique(
                ['learning_experience_id', 'attachable_type', 'attachable_id'],
                'le_attachables_unique'
            );
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('learning_experience_attachables');
        Schema::dropIfExists('learning_experience_attempts');
        Schema::dropIfExists('learning_experiences');
    }
};
