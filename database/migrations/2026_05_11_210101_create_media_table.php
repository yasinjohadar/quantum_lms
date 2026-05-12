<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('media', function (Blueprint $table) {
            $table->id();
            $table->uuid('uuid')->unique();
            $table->string('disk')->default('public');
            $table->string('path');
            $table->string('provider')->default('local');
            $table->string('visibility')->default('public');
            $table->string('mime_type')->nullable();
            $table->string('extension')->nullable();
            $table->unsignedBigInteger('size')->default(0);
            $table->string('checksum')->nullable();
            $table->foreignId('uploaded_by')->nullable()->constrained('users')->nullOnDelete();
            $table->string('storage_region')->nullable();
            $table->boolean('is_synced')->default(false);
            $table->string('sync_status')->default('pending');
            $table->unsignedInteger('reference_count')->default(0);
            $table->json('metadata')->nullable();
            $table->timestamp('deleted_at')->nullable();
            $table->timestamp('restored_at')->nullable();
            $table->timestamps();

            $table->unique(['path', 'deleted_at']);
            $table->index('checksum');
            $table->index(['disk', 'is_synced']);
            $table->index('provider');
            $table->index('reference_count');
            $table->index('sync_status');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('media');
    }
};
