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
        Schema::create('library_views', function (Blueprint $table) {
            $table->id();
            $table->foreignId('library_item_id')->constrained('library_items')->onDelete('cascade');
            $table->foreignId('user_id')->nullable()->constrained('users')->onDelete('set null')->comment('nullable للزوار غير المسجلين');
            $table->dateTime('viewed_at');
            $table->string('ip_address')->nullable();
            $table->integer('view_duration')->default(0)->comment('مدة المشاهدة بالثواني');
            $table->timestamps();
            
            // Indexes
            $table->index(['library_item_id', 'user_id']);
            $table->index('viewed_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('library_views');
    }
};
