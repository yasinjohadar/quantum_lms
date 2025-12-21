<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Lesson extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'lessons';

    protected $fillable = [
        'unit_id',
        'title',
        'description',
        'video_type',
        'video_url',
        'video_id',
        'thumbnail',
        'duration',
        'order',
        'is_active',
        'is_free',
        'is_preview',
    ];

    protected $casts = [
        'unit_id' => 'integer',
        'duration' => 'integer',
        'order' => 'integer',
        'is_active' => 'boolean',
        'is_free' => 'boolean',
        'is_preview' => 'boolean',
    ];

    /**
     * أنواع الفيديو المتاحة.
     */
    const VIDEO_TYPES = [
        'upload' => 'رفع مباشر',
        'youtube' => 'يوتيوب',
        'vimeo' => 'فيميو',
        'external' => 'رابط خارجي',
    ];

    /**
     * العلاقة مع الوحدة.
     */
    public function unit()
    {
        return $this->belongsTo(Unit::class);
    }

    /**
     * العلاقة مع المرفقات.
     */
    public function attachments()
    {
        return $this->hasMany(LessonAttachment::class)->orderBy('order');
    }

    /**
     * العلاقة مع محاولات الأسئلة.
     */
    public function questionAttempts()
    {
        return $this->hasMany(QuestionAttempt::class);
    }

    /**
     * العلاقة مع إكمالات الدروس.
     */
    public function completions()
    {
        return $this->hasMany(LessonCompletion::class);
    }

    /**
     * الحصول على مدة الفيديو بصيغة مقروءة.
     */
    public function getFormattedDurationAttribute()
    {
        if (!$this->duration) {
            return null;
        }

        $hours = floor($this->duration / 3600);
        $minutes = floor(($this->duration % 3600) / 60);
        $seconds = $this->duration % 60;

        if ($hours > 0) {
            return sprintf('%02d:%02d:%02d', $hours, $minutes, $seconds);
        }

        return sprintf('%02d:%02d', $minutes, $seconds);
    }

    /**
     * الحصول على رابط الفيديو للتشغيل.
     */
    public function getEmbedUrlAttribute()
    {
        // اكتشاف تلقائي لروابط YouTube و Vimeo حتى لو تم اختيار "رابط خارجي"
        $youtubeId = self::extractYoutubeId($this->video_url);
        $vimeoId = self::extractVimeoId($this->video_url);

        // لو الرابط هو YouTube
        if ($youtubeId) {
            return "https://www.youtube.com/embed/{$youtubeId}";
        }

        // لو الرابط هو Vimeo
        if ($vimeoId) {
            return "https://player.vimeo.com/video/{$vimeoId}";
        }

        // للملفات المرفوعة
        if ($this->video_type === 'upload' && $this->video_url) {
            return asset('storage/' . $this->video_url);
        }

        // رابط خارجي عادي
        return $this->video_url;
    }

    /**
     * الحصول على نوع الفيديو الفعلي (للعرض).
     */
    public function getActualVideoTypeAttribute()
    {
        if (self::extractYoutubeId($this->video_url)) {
            return 'youtube';
        }
        if (self::extractVimeoId($this->video_url)) {
            return 'vimeo';
        }
        return $this->video_type;
    }

    /**
     * استخراج معرف الفيديو من رابط YouTube.
     */
    public static function extractYoutubeId($url)
    {
        $pattern = '/(?:youtube\.com\/(?:[^\/]+\/.+\/|(?:v|e(?:mbed)?)\/|.*[?&]v=)|youtu\.be\/)([^"&?\/\s]{11})/';
        preg_match($pattern, $url, $matches);
        return $matches[1] ?? null;
    }

    /**
     * استخراج معرف الفيديو من رابط Vimeo.
     */
    public static function extractVimeoId($url)
    {
        $pattern = '/vimeo\.com\/(?:video\/)?(\d+)/';
        preg_match($pattern, $url, $matches);
        return $matches[1] ?? null;
    }

    /**
     * Scope للدروس النشطة.
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    /**
     * Scope للدروس المجانية.
     */
    public function scopeFree($query)
    {
        return $query->where('is_free', true);
    }

    /**
     * Scope للترتيب.
     */
    public function scopeOrdered($query)
    {
        return $query->orderBy('order');
    }
}

