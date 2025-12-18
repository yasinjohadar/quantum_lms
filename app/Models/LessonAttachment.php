<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class LessonAttachment extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'lesson_attachments';

    protected $fillable = [
        'lesson_id',
        'type',
        'title',
        'description',
        'file_path',
        'file_name',
        'file_type',
        'file_size',
        'url',
        'order',
        'is_downloadable',
        'is_active',
    ];

    protected $casts = [
        'lesson_id' => 'integer',
        'file_size' => 'integer',
        'order' => 'integer',
        'is_downloadable' => 'boolean',
        'is_active' => 'boolean',
    ];

    /**
     * أنواع المرفقات المتاحة.
     */
    const TYPES = [
        'file' => 'ملف',
        'link' => 'رابط',
        'document' => 'مستند',
        'image' => 'صورة',
        'audio' => 'صوت',
    ];

    /**
     * أيقونات أنواع المرفقات.
     */
    const TYPE_ICONS = [
        'file' => 'bi-file-earmark',
        'link' => 'bi-link-45deg',
        'document' => 'bi-file-earmark-pdf',
        'image' => 'bi-file-earmark-image',
        'audio' => 'bi-file-earmark-music',
    ];

    /**
     * العلاقة مع الدرس.
     */
    public function lesson()
    {
        return $this->belongsTo(Lesson::class);
    }

    /**
     * الحصول على حجم الملف بصيغة مقروءة.
     */
    public function getFormattedFileSizeAttribute()
    {
        if (!$this->file_size) {
            return null;
        }

        $bytes = $this->file_size;
        $units = ['B', 'KB', 'MB', 'GB'];

        for ($i = 0; $bytes > 1024 && $i < count($units) - 1; $i++) {
            $bytes /= 1024;
        }

        return round($bytes, 2) . ' ' . $units[$i];
    }

    /**
     * الحصول على أيقونة نوع المرفق.
     */
    public function getTypeIconAttribute()
    {
        return self::TYPE_ICONS[$this->type] ?? 'bi-file-earmark';
    }

    /**
     * الحصول على رابط التحميل أو العرض.
     */
    public function getAccessUrlAttribute()
    {
        if ($this->type === 'link') {
            return $this->url;
        }

        return $this->file_path ? asset('storage/' . $this->file_path) : null;
    }

    /**
     * Scope للمرفقات النشطة.
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    /**
     * Scope للترتيب.
     */
    public function scopeOrdered($query)
    {
        return $query->orderBy('order');
    }
}

