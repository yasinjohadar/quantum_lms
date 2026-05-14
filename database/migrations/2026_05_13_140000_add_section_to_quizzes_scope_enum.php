<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::getConnection()->getDriverName() === 'mysql') {
            DB::statement("ALTER TABLE quizzes MODIFY COLUMN scope ENUM('unit','lesson','section') NOT NULL DEFAULT 'unit'");
        }
    }

    public function down(): void
    {
        if (Schema::getConnection()->getDriverName() === 'mysql') {
            DB::statement("UPDATE quizzes SET scope = 'unit' WHERE scope = 'section'");
            DB::statement("ALTER TABLE quizzes MODIFY COLUMN scope ENUM('unit','lesson') NOT NULL DEFAULT 'unit'");
        }
    }
};
