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
        $socialKeys = [
            'social_facebook_url'  => 'رابط فيسبوك',
            'social_instagram_url' => 'رابط انستغرام',
            'social_telegram_url'  => 'رابط تيليجرام',
            'social_youtube_url'   => 'رابط يوتيوب',
        ];

        foreach ($socialKeys as $key => $description) {
            if (! SystemSetting::where('key', $key)->exists()) {
                SystemSetting::set($key, '', 'string', 'social', $description . ' — اتركه فارغاً لإخفاء الرابط من الهيدر والفوتر');
            }
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        SystemSetting::whereIn('key', [
            'social_facebook_url',
            'social_instagram_url',
            'social_telegram_url',
            'social_youtube_url',
        ])->delete();
    }
};
