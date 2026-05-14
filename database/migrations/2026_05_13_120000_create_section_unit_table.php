<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * ظهور وحدة (سجل واحد في units) في أقسام إضافية دون تغيير section_id المنزل.
     */
    public function up(): void
    {
        Schema::create('section_unit', function (Blueprint $table) {
            $table->id();
            $table->foreignId('subject_section_id')->constrained('subject_sections')->cascadeOnDelete();
            $table->foreignId('unit_id')->constrained('units')->cascadeOnDelete();
            $table->unsignedInteger('order')->default(0);
            $table->timestamps();

            $table->unique(['subject_section_id', 'unit_id']);
            $table->index(['subject_section_id', 'order']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('section_unit');
    }
};
