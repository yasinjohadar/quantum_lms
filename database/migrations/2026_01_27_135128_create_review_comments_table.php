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
        Schema::create('review_comments', function (Blueprint $table) {
            $table->id();
            $table->morphs('reviewable'); // reviewable_type, reviewable_id
            $table->foreignId('user_id')->constrained('users')->onDelete('cascade')->comment('المستخدم الذي كتب الملاحظة');
            $table->foreignId('parent_id')->nullable()->constrained('review_comments')->onDelete('cascade')->comment('الملاحظة الرئيسية (للردود)');
            $table->text('comment')->comment('نص الملاحظة');
            $table->boolean('is_resolved')->default(false)->comment('تم حل الملاحظة');
            $table->timestamp('resolved_at')->nullable()->comment('تاريخ الحل');
            $table->timestamps();

            // Indexes
            $table->index('user_id');
            $table->index('parent_id');
            $table->index('is_resolved');
            $table->index('created_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('review_comments');
    }
};
