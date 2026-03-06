<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('social_links', function (Blueprint $table) {
            $table->id();
            $table->string('name');           // اسم العرض (مثل: فيسبوك، انستغرام)
            $table->string('url');
            $table->string('icon_class', 100); // Font Awesome مثل: fa-brands fa-facebook-f
            $table->unsignedSmallInteger('sort_order')->default(0);
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        // نقل الروابط الحالية من system_settings إن وُجدت
        $keys = [
            'social_facebook_url'  => ['فيسبوك', 'fa-brands fa-facebook-f'],
            'social_instagram_url' => ['انستغرام', 'fa-brands fa-instagram'],
            'social_telegram_url'  => ['تيليجرام', 'fa-brands fa-telegram'],
            'social_youtube_url'   => ['يوتيوب', 'fa-brands fa-youtube'],
        ];
        $sort = 0;
        foreach ($keys as $key => [$label, $icon]) {
            $value = DB::table('system_settings')->where('key', $key)->value('value');
            if ($value !== null && trim((string) $value) !== '') {
                DB::table('social_links')->insert([
                    'name'       => $label,
                    'url'        => trim($value),
                    'icon_class' => $icon,
                    'sort_order' => $sort++,
                    'is_active'  => true,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            }
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('social_links');
    }
};
