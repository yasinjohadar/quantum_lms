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
        Schema::create('library_ratings', function (Blueprint $table) {
            $table->id();
            $table->foreignId('library_item_id')->constrained('library_items')->onDelete('cascade');
            $table->foreignId('user_id')->constrained('users')->onDelete('cascade');
            $table->integer('rating')->comment('التقييم من 1-5');
            $table->text('comment')->nullable()->comment('تعليق اختياري');
            $table->timestamps();
            
            // Indexes
            $table->index(['library_item_id', 'user_id']);
            $table->unique(['library_item_id', 'user_id'], 'unique_item_user_rating');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('library_ratings');
    }
};
