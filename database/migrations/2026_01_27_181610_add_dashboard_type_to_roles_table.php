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
        Schema::table('roles', function (Blueprint $table) {
            $table->enum('dashboard_type', ['admin', 'student'])
                  ->default('student')
                  ->after('guard_name');
        });

        // تحديث الأدوار الموجودة لضمان القيم الصحيحة
        DB::table('roles')->whereIn('name', ['admin', 'supervisor', 'teacher'])->update(['dashboard_type' => 'admin']);
        DB::table('roles')->where('name', 'student')->update(['dashboard_type' => 'student']);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('roles', function (Blueprint $table) {
            $table->dropColumn('dashboard_type');
        });
    }
};
