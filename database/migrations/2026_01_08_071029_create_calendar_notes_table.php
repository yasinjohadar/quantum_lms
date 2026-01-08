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
        Schema::create('calendar_notes', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users')->onDelete('cascade');
            $table->date('note_date');
            $table->string('title')->nullable();
            $table->text('content');
            $table->string('color')->default('#fbbf24')->comment('لون الملاحظة');
            $table->boolean('is_pinned')->default(false)->comment('ملاحظة مثبتة');
            $table->timestamps();
            $table->softDeletes();

            // Indexes
            $table->index('user_id');
            $table->index('note_date');
            $table->index(['user_id', 'note_date']);
            $table->index('is_pinned');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('calendar_notes');
    }
};
