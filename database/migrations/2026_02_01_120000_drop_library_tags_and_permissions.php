<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::dropIfExists('library_item_tags');
        Schema::dropIfExists('library_tags');

        DB::table('permissions')->where('name', 'like', 'library-tag-%')->delete();
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::create('library_tags', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('slug')->unique();
            $table->string('color')->nullable();
            $table->timestamps();
            $table->index('slug');
        });

        Schema::create('library_item_tags', function (Blueprint $table) {
            $table->id();
            $table->foreignId('library_item_id')->constrained('library_items')->onDelete('cascade');
            $table->foreignId('tag_id')->constrained('library_tags')->onDelete('cascade');
            $table->timestamps();
            $table->unique(['library_item_id', 'tag_id'], 'unique_item_tag');
        });
    }
};
