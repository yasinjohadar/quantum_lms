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
        Schema::create('archived_users', function (Blueprint $table) {
            $table->id();
            
            // Reference to original user (cascade to allow soft delete, but archived record remains)
            $table->foreignId('original_user_id')->unique()->constrained('users')->onDelete('cascade');
            
            // Copy all user columns
            $table->string('name');
            $table->string('email')->unique();
            $table->timestamp('email_verified_at')->nullable();
            $table->string('password');
            $table->string('phone')->nullable();
            $table->string('avatar')->nullable();
            $table->string('student_id')->nullable();
            $table->date('date_of_birth')->nullable();
            $table->enum('gender', ['male', 'female'])->nullable();
            $table->timestamp('last_login_at')->nullable();
            $table->string('last_login_ip', 45)->nullable();
            $table->string('last_device_type')->nullable();
            $table->boolean('is_active')->default(true);
            $table->boolean('is_connected')->default(false);
            $table->text('address')->nullable();
            $table->rememberToken();
            $table->softDeletes();
            
            // Archive-specific fields
            $table->timestamp('archived_at');
            $table->foreignId('archived_by')->constrained('users')->onDelete('cascade');
            $table->text('archive_reason')->nullable();
            $table->timestamp('restored_at')->nullable();
            $table->foreignId('restored_by')->nullable()->constrained('users')->onDelete('set null');
            
            $table->timestamps();
            
            // Indexes
            $table->index('archived_at');
            $table->index('archived_by');
            $table->index('is_active');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('archived_users');
    }
};
