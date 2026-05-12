<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('media_usages', function (Blueprint $table) {
            $table->id();
            $table->foreignId('media_id')->constrained('media')->cascadeOnDelete();
            $table->string('model_type');
            $table->unsignedBigInteger('model_id');
            $table->string('collection')->default('default');
            $table->string('field')->nullable();
            $table->string('usage_context')->nullable();
            $table->timestamps();

            $table->index(['model_type', 'model_id']);
            $table->index(['collection', 'field']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('media_usages');
    }
};
