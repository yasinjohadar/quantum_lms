<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('roles', function (Blueprint $table) {
            $table->string('staff_profile', 32)
                ->default('none')
                ->after('dashboard_type');
        });

        try {
            DB::table('roles')->where('name', 'supervisor')->update(['staff_profile' => 'supervisor']);
            DB::table('roles')->whereIn('name', [
                'supervisor-content-review',
                'supervisor-quiz-followup',
            ])->update(['staff_profile' => 'supervisor']);

            DB::table('roles')->where('name', 'teacher')->update(['staff_profile' => 'teacher']);
            DB::table('roles')->whereIn('name', [
                'teacher-content-uploader',
                'teacher-assistant',
                'teacher-quiz-followup',
            ])->update(['staff_profile' => 'teacher']);

            DB::table('roles')->whereIn('name', ['admin', 'student'])->update(['staff_profile' => 'none']);
        } catch (\Throwable $e) {
            // تجاهل إن كان الجدول فارغاً أو الأعمدة غير متوافقة
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('roles', function (Blueprint $table) {
            $table->dropColumn('staff_profile');
        });
    }
};
