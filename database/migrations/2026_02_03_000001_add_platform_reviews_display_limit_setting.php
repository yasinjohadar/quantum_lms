<?php

use Illuminate\Database\Migrations\Migration;
use App\Models\SystemSetting;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        SystemSetting::set(
            'platform_reviews_display_limit',
            '6',
            'integer',
            'general',
            'عدد آراء الطلاب المعروضة في السلايدر بالصفحة الرئيسية'
        );
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        SystemSetting::where('key', 'platform_reviews_display_limit')->delete();
    }
};
