<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use App\Models\SystemSetting;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('purchases', function (Blueprint $table) {
            $table->timestamp('cancelled_at')->nullable()->after('status');
            $table->enum('cancelled_by', ['student', 'admin'])->nullable()->after('cancelled_at');
        });

        if (! SystemSetting::where('key', 'student_supervisor_whatsapp_number')->where('group', 'general')->exists()) {
            SystemSetting::set(
                'student_supervisor_whatsapp_number',
                '',
                'string',
                'general',
                'رقم واتساب مشرفة الطلاب لمتابعة التفعيل (مثال: 963912345678)'
            );
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        SystemSetting::where('key', 'student_supervisor_whatsapp_number')->where('group', 'general')->delete();

        Schema::table('purchases', function (Blueprint $table) {
            $table->dropColumn(['cancelled_at', 'cancelled_by']);
        });
    }
};
