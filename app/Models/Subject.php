<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Str;

class Subject extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'subjects';

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'name',
        'slug',
        'class_id',
        'image',
        'description',
        'meta_title',
        'meta_description',
        'meta_keywords',
        'og_image',
        'order',
        'is_active',
        'display_in_class',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'is_active' => 'boolean',
        'display_in_class' => 'boolean',
        'order' => 'integer',
        'class_id' => 'integer',
    ];

    protected static function boot()
    {
        parent::boot();

        static::creating(function (Subject $subject) {
            if (empty($subject->slug)) {
                $subject->slug = Str::slug($subject->name . '-' . ($subject->class_id ?? ''));
            }
        });

        static::updating(function (Subject $subject) {
            if (empty($subject->slug)) {
                $subject->slug = Str::slug($subject->name . '-' . ($subject->class_id ?? ''));
            }
        });
    }

    /**
     * العلاقة مع الصف الدراسي.
     */
    public function schoolClass()
    {
        return $this->belongsTo(SchoolClass::class, 'class_id');
    }

    /**
     * الأقسام التابعة لهذه المادة.
     */
    public function sections()
    {
        return $this->hasMany(SubjectSection::class, 'subject_id')->orderBy('order');
    }

    /**
     * نطاق المواد النشطة فقط.
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    /**
     * نطاق ترتيب المواد.
     */
    public function scopeOrdered($query)
    {
        return $query->orderBy('order', 'asc')->orderBy('name', 'asc');
    }

    /**
     * نطاق البحث في المواد.
     */
    public function scopeSearch($query, $search)
    {
        if (! $search) {
            return $query;
        }

        return $query->where(function ($q) use ($search) {
            $q->where('name', 'like', '%' . $search . '%')
              ->orWhere('description', 'like', '%' . $search . '%')
              ->orWhere('meta_title', 'like', '%' . $search . '%')
              ->orWhere('meta_description', 'like', '%' . $search . '%');
        });
    }

    /**
     * نطاق الفلترة حسب الصف.
     */
    public function scopeByClass($query, $classId)
    {
        if (! $classId) {
            return $query;
        }

        return $query->where('class_id', $classId);
    }

    /**
     * العلاقة مع الانضمامات
     */
    public function enrollments()
    {
        return $this->hasMany(Enrollment::class, 'subject_id');
    }

    /**
     * العلاقة مع الطلاب (Many-to-Many through enrollments)
     */
    public function students()
    {
        return $this->belongsToMany(User::class, 'enrollments', 'subject_id', 'user_id')
                    ->withPivot(['enrolled_by', 'enrolled_at', 'status', 'notes'])
                    ->withTimestamps();
    }
}

