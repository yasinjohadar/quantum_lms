<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('storage_sync_dead_letters', function (Blueprint $table) {
            $table->id();
            $table->string('file_path');
            $table->string('target_disk');
            $table->foreignId('batch_id')->nullable()->constrained('storage_sync_batches')->nullOnDelete();
            $table->text('error');
            $table->integer('attempts')->default(0);
            $table->boolean('resolved')->default(false);
            $table->timestamp('resolved_at')->nullable();
            $table->timestamps();

            $table->index(['resolved', 'created_at']);
            $table->index('target_disk');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('storage_sync_dead_letters');
    }
};
