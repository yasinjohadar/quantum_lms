<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class LibraryItem extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'title',
        'slug',
        'description',
        'type',
        'category_id',
        'class_id',
        'subject_id',
        'uploaded_by',
        'file_path',
        'file_name',
        'file_type',
        'file_size',
        'external_url',
        'thumbnail',
        'is_featured',
        'is_public',
        'access_level',
        'download_count',
        'view_count',
        'average_rating',
        'total_ratings',
    ];

    protected $casts = [
        'is_featured' => 'boolean',
        'is_public' => 'boolean',
        'file_size' => 'integer',
        'download_count' => 'integer',
        'view_count' => 'integer',
        'total_ratings' => 'integer',
        'average_rating' => 'decimal:2',
    ];

    /**
     * أنواع العناصر المتاحة
     */
    public const TYPES = [
        'file' => 'ملف',
        'link' => 'رابط',
        'video' => 'فيديو',
        'document' => 'مستند',
        'book' => 'كتاب',
        'worksheet' => 'ورقة عمل',
    ];

    /**
     * مستويات الوصول
     */
    public const ACCESS_LEVELS = [
        'public' => 'عام',
        'enrolled' => 'مسجل في المادة',
        'restricted' => 'مقيد',
    ];

    protected static function boot()
    {
        parent::boot();

        static::creating(function (LibraryItem $item) {
            if (empty($item->slug)) {
                $item->slug = Str::slug($item->title);
            }
        });

        static::updating(function (LibraryItem $item) {
            if ($item->isDirty('title') && empty($item->slug)) {
                $item->slug = Str::slug($item->title);
            }
        });
    }

    /**
     * العلاقة مع التصنيف
     */
    public function category()
    {
        return $this->belongsTo(LibraryCategory::class, 'category_id');
    }

    /**
     * العلاقة مع الصف الدراسي
     */
    public function schoolClass()
    {
        return $this->belongsTo(SchoolClass::class, 'class_id');
    }

    /**
     * العلاقة مع المادة
     */
    public function subject()
    {
        return $this->belongsTo(Subject::class, 'subject_id');
    }

    /**
     * العلاقة مع من رفع العنصر
     */
    public function uploader()
    {
        return $this->belongsTo(User::class, 'uploaded_by');
    }

    /**
     * العلاقة مع التحميلات
     */
    public function downloads()
    {
        return $this->hasMany(LibraryDownload::class, 'library_item_id');
    }

    /**
     * العلاقة مع المشاهدات
     */
    public function views()
    {
        return $this->hasMany(LibraryView::class, 'library_item_id');
    }

    /**
     * العلاقة مع التقييمات
     */
    public function ratings()
    {
        return $this->hasMany(LibraryRating::class, 'library_item_id');
    }

    /**
     * العلاقة مع الوسوم (many-to-many)
     */
    public function tags()
    {
        return $this->belongsToMany(LibraryTag::class, 'library_item_tags', 'library_item_id', 'tag_id');
    }

    /**
     * العلاقة مع المفضلة (many-to-many)
     */
    public function favorites()
    {
        return $this->hasMany(LibraryFavorite::class, 'library_item_id');
    }

    /**
     * العلاقة مع المستخدمين الذين أضافوا العنصر للمفضلة
     */
    public function favoritedByUsers()
    {
        return $this->belongsToMany(User::class, 'library_favorites', 'library_item_id', 'user_id');
    }

    /**
     * نطاق العناصر العامة
     */
    public function scopePublic($query)
    {
        return $query->where('is_public', true);
    }

    /**
     * نطاق العناصر المميزة
     */
    public function scopeFeatured($query)
    {
        return $query->where('is_featured', true);
    }

    /**
     * نطاق العناصر حسب النوع
     */
    public function scopeByType($query, string $type)
    {
        return $query->where('type', $type);
    }

    /**
     * نطاق العناصر حسب التصنيف
     */
    public function scopeByCategory($query, int $categoryId)
    {
        return $query->where('category_id', $categoryId);
    }

    /**
     * نطاق العناصر لمادة معينة
     */
    public function scopeForSubject($query, int $subjectId)
    {
        return $query->where('subject_id', $subjectId);
    }

    /**
     * نطاق العناصر لصف معين
     */
    public function scopeForClass($query, int $classId)
    {
        return $query->where('class_id', $classId);
    }

    /**
     * الحصول على رابط الملف
     */
    public function getFileUrl(): ?string
    {
        if (!$this->file_path) {
            return null;
        }
        return Storage::disk('public')->url($this->file_path);
    }

    /**
     * الحصول على رابط الصورة المصغرة
     */
    public function getThumbnailUrl(): ?string
    {
        if (!$this->thumbnail) {
            return null;
        }
        return Storage::disk('public')->url($this->thumbnail);
    }

    /**
     * التحقق من إمكانية تحميل المستخدم للعنصر
     */
    public function canUserDownload(?User $user): bool
    {
        // إذا كان الوصول عام
        if ($this->access_level === 'public' && $this->is_public) {
            // الكتب العامة (غير مرتبطة بمادة أو صف) متاحة للجميع
            if (is_null($this->subject_id) && is_null($this->class_id)) {
                return true;
            }
            
            // إذا لم يكن هناك مستخدم مسجل، لا يمكن الوصول للكتب المرتبطة بمادة أو صف
            if (!$user) {
                return false;
            }
            
            // التحقق من التسجيل في المادة (إذا كانت مرتبطة بمادة)
            if ($this->subject_id) {
                $isEnrolledInSubject = $this->subject->students()
                    ->where('users.id', $user->id)
                    ->exists();
                if ($isEnrolledInSubject) {
                    return true;
                }
            }
            
            // التحقق من التسجيل في الصف (إذا كانت مرتبطة بصف)
            if ($this->class_id) {
                $isEnrolledInClass = $user->classEnrollments()
                    ->where('class_id', $this->class_id)
                    ->approved()
                    ->exists();
                if ($isEnrolledInClass) {
                    return true;
                }
            }
            
            // إذا كانت مرتبطة بمادة أو صف لكن المستخدم غير مسجل، لا يمكن الوصول
            return false;
        }

        // إذا لم يكن هناك مستخدم مسجل
        if (!$user) {
            return false;
        }

        // إذا كان الوصول للمسجلين في المادة
        if ($this->access_level === 'enrolled' && $this->subject_id) {
            return $this->subject->students()->where('users.id', $user->id)->exists();
        }

        // إذا كان الوصول مقيد (يحتاج صلاحيات خاصة)
        if ($this->access_level === 'restricted') {
            return $user->hasRole(['admin', 'teacher']);
        }

        return false;
    }

    /**
     * زيادة عدد المشاهدات
     */
    public function incrementView(?User $user = null, ?int $duration = 0): void
    {
        $this->increment('view_count');
        
        // تسجيل المشاهدة
        $this->views()->create([
            'user_id' => $user?->id,
            'viewed_at' => now(),
            'ip_address' => request()->ip(),
            'view_duration' => $duration,
        ]);
    }

    /**
     * زيادة عدد التحميلات
     */
    public function incrementDownload(User $user): void
    {
        $this->increment('download_count');
        
        // تسجيل التحميل
        $this->downloads()->create([
            'user_id' => $user->id,
            'downloaded_at' => now(),
            'ip_address' => request()->ip(),
            'user_agent' => request()->userAgent(),
        ]);
    }

    /**
     * حساب متوسط التقييم
     */
    public function calculateAverageRating(): void
    {
        $ratings = $this->ratings;
        $totalRatings = $ratings->count();
        
        if ($totalRatings > 0) {
            $averageRating = $ratings->avg('rating');
            $this->update([
                'average_rating' => round($averageRating, 2),
                'total_ratings' => $totalRatings,
            ]);
        } else {
            $this->update([
                'average_rating' => 0,
                'total_ratings' => 0,
            ]);
        }
    }

    /**
     * الحصول على حجم الملف بصيغة مقروءة
     */
    public function getFormattedFileSizeAttribute(): ?string
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
}
