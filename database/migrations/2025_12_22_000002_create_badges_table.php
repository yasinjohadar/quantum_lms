<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('badges', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->text('description')->nullable();
            $table->string('icon')->nullable();
            $table->string('color')->default('#007bff');
            $table->integer('points_required')->default(0);
            $table->json('criteria')->nullable(); // معايير الحصول على الشارة
            $table->boolean('is_active')->default(true);
            $table->boolean('is_automatic')->default(true); // منح تلقائي أم يدوي
            $table->integer('order')->default(0);
            $table->timestamps();
            $table->softDeletes();

            $table->index('is_active');
            $table->index('order');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('badges');
    }
};

