<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SocialLink extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'url',
        'icon_class',
        'sort_order',
        'is_active',
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'sort_order' => 'integer',
    ];

    public function scopeOrdered($query)
    {
        return $query->orderBy('sort_order')->orderBy('id');
    }

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    /**
     * قائمة أيقونات مقترحة (Font Awesome) للاختيار في النموذج
     */
    public static function suggestedIcons(): array
    {
        return [
            'fa-brands fa-facebook-f'   => 'فيسبوك',
            'fa-brands fa-instagram'     => 'انستغرام',
            'fa-brands fa-telegram'      => 'تيليجرام',
            'fa-brands fa-youtube'       => 'يوتيوب',
            'fa-brands fa-twitter'       => 'تويتر / X',
            'fa-brands fa-x-twitter'     => 'X (تويتر)',
            'fa-brands fa-linkedin-in'   => 'لينكد إن',
            'fa-brands fa-tiktok'       => 'تيك توك',
            'fa-brands fa-snapchat-ghost' => 'سناب شات',
            'fa-brands fa-whatsapp'     => 'واتساب',
            'fa-brands fa-pinterest-p'  => 'بينترست',
            'fa-brands fa-discord'      => 'ديسكورد',
            'fa-brands fa-github'       => 'جيت هب',
            'fa-brands fa-dribbble'     => 'دريببل',
            'fa-brands fa-behance'      => 'بيهانس',
        ];
    }
}
