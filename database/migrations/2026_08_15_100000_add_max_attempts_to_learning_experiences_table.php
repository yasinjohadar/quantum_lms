<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('learning_experiences', function (Blueprint $table) {
            $table->integer('max_attempts')->default(0)->comment('عدد المحاولات (0 = غير محدود)')->after('passing_score');
        });
    }

    public function down(): void
    {
        Schema::table('learning_experiences', function (Blueprint $table) {
            $table->dropColumn('max_attempts');
        });
    }
};
