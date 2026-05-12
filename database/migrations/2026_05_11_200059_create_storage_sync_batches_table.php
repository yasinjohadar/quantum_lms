<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('storage_sync_batches', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('disk_name');
            $table->integer('total_files')->default(0);
            $table->integer('processed_files')->default(0);
            $table->integer('successful_files')->default(0);
            $table->integer('failed_files')->default(0);
            $table->string('status')->default('running');
            $table->json('errors')->nullable();
            $table->timestamp('started_at')->nullable();
            $table->timestamp('completed_at')->nullable();
            $table->foreignId('started_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
            
            $table->index(['status', 'disk_name']);
            $table->index('started_at');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('storage_sync_batches');
    }
};
