<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('media_metrics', function (Blueprint $table) {
            $table->id();
            $table->string('metric_type');
            $table->string('metric_name');
            $table->json('data')->nullable();
            $table->float('value')->default(0);
            $table->string('unit')->nullable();
            $table->string('provider')->nullable();
            $table->timestamp('recorded_at');
            $table->timestamps();

            $table->index(['metric_type', 'metric_name']);
            $table->index('recorded_at');
            $table->index('provider');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('media_metrics');
    }
};
