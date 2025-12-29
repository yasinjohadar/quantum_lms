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
        Schema::create('zoom_accounts', function (Blueprint $table) {
            $table->id();
            $table->string('name'); // اسم الحساب
            $table->enum('type', ['api', 'oauth'])->default('api'); // نوع الحساب: api (Server-to-Server) أو oauth (OAuth App)
            $table->string('account_id')->nullable(); // للـ Server-to-Server OAuth
            $table->string('client_id');
            $table->text('client_secret'); // مشفر
            $table->string('sdk_key')->nullable(); // للـ Meeting SDK
            $table->text('sdk_secret')->nullable(); // مشفر - للـ Meeting SDK
            $table->string('redirect_uri')->nullable(); // للـ OAuth App
            $table->boolean('is_default')->default(false); // الحساب الافتراضي
            $table->boolean('is_active')->default(true);
            $table->text('description')->nullable();
            $table->foreignId('created_by')->nullable()->constrained('users')->onDelete('set null');
            $table->timestampsTz();

            // Indexes
            $table->index('type');
            $table->index('is_default');
            $table->index('is_active');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('zoom_accounts');
    }
};
