<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('levels', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->integer('level_number');
            $table->integer('points_required');
            $table->string('icon')->nullable();
            $table->string('color')->default('#007bff');
            $table->json('benefits')->nullable(); // مزايا المستوى
            $table->integer('order')->default(0);
            $table->timestamps();
            $table->softDeletes();

            $table->unique('level_number');
            $table->index('points_required');
            $table->index('order');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('levels');
    }
};

