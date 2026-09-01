<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
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
        'is_featured',
        'is_public',
        'access_level',
    ];

    protected $casts = [
        'is_featured' => 'boolean',
        'is_public' => 'boolean',
        'file_size' => 'integer',
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

    public function category()
    {
        return $this->belongsTo(LibraryCategory::class, 'category_id');
    }

    public function schoolClass()
    {
        return $this->belongsTo(SchoolClass::class, 'class_id');
    }

    public function subject()
    {
        return $this->belongsTo(Subject::class, 'subject_id');
    }

    public function uploader()
    {
        return $this->belongsTo(User::class, 'uploaded_by');
    }

    public function scopePublic($query)
    {
        return $query->where('is_public', true);
    }

    public function scopeFeatured($query)
    {
        return $query->where('is_featured', true);
    }

    public function scopeByType($query, string $type)
    {
        return $query->where('type', $type);
    }

    public function scopeByCategory($query, int $categoryId)
    {
        return $query->where('category_id', $categoryId);
    }

    public function scopeForSubject($query, int $subjectId)
    {
        return $query->where('subject_id', $subjectId);
    }

    public function scopeForClass($query, int $classId)
    {
        return $query->where('class_id', $classId);
    }

    /**
     * التحقق من إمكانية تحميل المستخدم للعنصر.
     * منطق مطابق للنسخة القديمة من الميزة (قبل حذفها) — يعمل دون تعديل مع علاقات اليوم.
     */
    public function canUserDownload(?User $user): bool
    {
        // إذا كان الوصول عام
        if ($this->access_level === 'public' && $this->is_public) {
            // العناصر العامة (غير مرتبطة بمادة أو صف) متاحة للجميع
            if (is_null($this->subject_id) && is_null($this->class_id)) {
                return true;
            }

            // إذا لم يكن هناك مستخدم مسجل، لا يمكن الوصول للعناصر المرتبطة بمادة أو صف
            if (! $user) {
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
        if (! $user) {
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

    public function getFormattedFileSizeAttribute(): ?string
    {
        if (! $this->file_size) {
            return null;
        }

        $bytes = $this->file_size;
        $units = ['B', 'KB', 'MB', 'GB'];

        for ($i = 0; $bytes > 1024 && $i < count($units) - 1; $i++) {
            $bytes /= 1024;
        }

        return round($bytes, 2).' '.$units[$i];
    }
}
