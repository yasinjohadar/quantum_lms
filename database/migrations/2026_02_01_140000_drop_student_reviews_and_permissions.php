<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     * Remove student reviews feature: drop review_votes, reviews, remove review columns from subjects/classes, delete review permissions.
     */
    public function up(): void
    {
        Schema::dropIfExists('review_votes');
        Schema::dropIfExists('reviews');

        Schema::table('subjects', function (Blueprint $table) {
            $table->dropColumn(['reviews_enabled', 'reviews_require_approval']);
        });

        Schema::table('classes', function (Blueprint $table) {
            $table->dropColumn(['reviews_enabled', 'reviews_require_approval']);
        });

        DB::table('permissions')->whereIn('name', [
            'review-list',
            'review-create',
            'review-edit',
            'review-delete',
        ])->delete();
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('subjects', function (Blueprint $table) {
            $table->boolean('reviews_enabled')->default(true)->after('is_active');
            $table->boolean('reviews_require_approval')->default(true)->after('reviews_enabled');
        });

        Schema::table('classes', function (Blueprint $table) {
            $table->boolean('reviews_enabled')->default(true)->after('is_active');
            $table->boolean('reviews_require_approval')->default(true)->after('reviews_enabled');
        });

        Schema::create('reviews', function (Blueprint $table) {
            $table->id();
            $table->morphs('reviewable');
            $table->foreignId('user_id')->constrained('users')->onDelete('cascade');
            $table->tinyInteger('rating')->unsigned()->comment('1-5 stars');
            $table->string('title')->nullable();
            $table->text('comment')->nullable();
            $table->enum('status', ['pending', 'approved', 'rejected'])->default('pending');
            $table->foreignId('approved_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('approved_at')->nullable();
            $table->text('rejected_reason')->nullable();
            $table->integer('is_helpful_count')->default(0);
            $table->boolean('is_anonymous')->default(false);
            $table->timestamps();
            $table->softDeletes();
            $table->index('user_id');
            $table->index('status');
            $table->index('rating');
            $table->index('created_at');
        });

        Schema::create('review_votes', function (Blueprint $table) {
            $table->id();
            $table->foreignId('review_id')->constrained('reviews')->onDelete('cascade');
            $table->foreignId('user_id')->constrained('users')->onDelete('cascade');
            $table->boolean('is_helpful')->default(true);
            $table->timestamps();
            $table->unique(['review_id', 'user_id']);
            $table->index('review_id');
            $table->index('user_id');
        });

        // Permissions would need to be re-seeded; not restoring here
    }
};
