<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Storage;

class HeroSlide extends Model
{
    use HasFactory;

    protected $fillable = [
        'title',
        'subtitle',
        'description',
        'button_text',
        'button_url',
        'button2_text',
        'button2_url',
        'background_image',
        'content_image',
        'text_position',
        'order',
        'is_active',
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'order' => 'integer',
    ];

    /**
     * Scope: شرائح نشطة فقط
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    /**
     * Scope: ترتيب حسب order
     */
    public function scopeOrdered($query)
    {
        return $query->orderBy('order');
    }

    /**
     * الرابط الكامل لصورة الخلفية
     */
    public function getBackgroundImageUrlAttribute(): ?string
    {
        if (!$this->background_image) {
            return null;
        }
        return asset('storage/' . $this->background_image);
    }

    /**
     * الرابط الكامل لصورة المحتوى
     */
    public function getContentImageUrlAttribute(): ?string
    {
        if (!$this->content_image) {
            return null;
        }
        return asset('storage/' . $this->content_image);
    }
}
