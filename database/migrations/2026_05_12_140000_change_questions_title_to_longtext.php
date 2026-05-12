<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * نص السؤال (title) يُخزَّن كـ HTML من TinyMCE وقد يتضمّن روابط صور طويلة؛ VARCHAR الافتراضي غير كافٍ.
     */
    public function up(): void
    {
        $driver = Schema::getConnection()->getDriverName();

        if ($driver === 'mysql') {
            DB::statement('ALTER TABLE `questions` MODIFY `title` LONGTEXT NOT NULL');
        } elseif ($driver === 'mariadb') {
            DB::statement('ALTER TABLE `questions` MODIFY `title` LONGTEXT NOT NULL');
        } else {
            Schema::table('questions', function (Blueprint $table) {
                $table->longText('title')->change();
            });
        }
    }

    public function down(): void
    {
        $driver = Schema::getConnection()->getDriverName();

        if ($driver === 'mysql' || $driver === 'mariadb') {
            DB::statement('ALTER TABLE `questions` MODIFY `title` VARCHAR(255) NOT NULL');
        } else {
            Schema::table('questions', function (Blueprint $table) {
                $table->string('title')->change();
            });
        }
    }
};
