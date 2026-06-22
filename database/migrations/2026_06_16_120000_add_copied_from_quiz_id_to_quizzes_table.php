<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('quizzes', function (Blueprint $table) {
            $table->foreignId('copied_from_quiz_id')
                ->nullable()
                ->after('lesson_id')
                ->constrained('quizzes')
                ->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('quizzes', function (Blueprint $table) {
            $table->dropForeign(['copied_from_quiz_id']);
            $table->dropColumn('copied_from_quiz_id');
        });
    }
};
