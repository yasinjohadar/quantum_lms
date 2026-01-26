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
        Schema::table('lessons', function (Blueprint $table) {
            $table->integer('book_page_from')->nullable()->after('duration')->comment('الصفحة الأولى من الكتاب');
            $table->integer('book_page_to')->nullable()->after('book_page_from')->comment('الصفحة الأخيرة من الكتاب');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('lessons', function (Blueprint $table) {
            $table->dropColumn(['book_page_from', 'book_page_to']);
        });
    }
};
