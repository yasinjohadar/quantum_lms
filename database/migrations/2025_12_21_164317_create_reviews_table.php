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
        Schema::create('reviews', function (Blueprint $table) {
            $table->id();
            $table->morphs('reviewable'); // reviewable_type, reviewable_id (this already creates an index)
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

            // Additional Indexes (morphs already creates index for reviewable_type and reviewable_id)
            $table->index('user_id');
            $table->index('status');
            $table->index('rating');
            $table->index('created_at');

            // Unique constraint: user can only have one review per reviewable (unless deleted)
            // Note: Laravel's unique() doesn't support soft deletes directly, so we'll handle this in the application logic
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('reviews');
    }
};
