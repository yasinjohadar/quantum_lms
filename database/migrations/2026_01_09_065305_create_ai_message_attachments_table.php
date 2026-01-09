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
        Schema::create('ai_message_attachments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('message_id')->constrained('ai_messages')->onDelete('cascade');
            $table->string('file_name');
            $table->string('file_path');
            $table->string('file_type')->comment('image, document, etc');
            $table->string('mime_type')->nullable();
            $table->integer('file_size')->comment('حجم الملف بالبايت');
            $table->text('content')->nullable()->comment('للصور: base64، للملفات: النص المستخرج');
            $table->timestamps();

            // Indexes
            $table->index('message_id');
            $table->index('file_type');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('ai_message_attachments');
    }
};
