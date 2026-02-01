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
        Schema::dropIfExists('certificates');
        Schema::dropIfExists('certificate_templates');

        DB::table('permissions')->where('name', 'like', 'certificate-%')->delete();
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::create('certificate_templates', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->text('description')->nullable();
            $table->string('type');
            $table->text('template_html')->nullable();
            $table->json('settings')->nullable();
            $table->boolean('is_active')->default(true);
            $table->boolean('is_default')->default(false);
            $table->timestamps();
            $table->softDeletes();
            $table->index('type');
            $table->index('is_active');
        });

        Schema::create('certificates', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->foreignId('subject_id')->nullable()->constrained()->onDelete('set null');
            $table->string('type');
            $table->string('certificate_number')->unique();
            $table->timestamp('issued_at');
            $table->foreignId('template_id')->nullable()->constrained('certificate_templates')->onDelete('set null');
            $table->json('metadata')->nullable();
            $table->string('pdf_path')->nullable();
            $table->timestamps();
            $table->index(['user_id', 'type']);
            $table->index('issued_at');
        });
    }
};
