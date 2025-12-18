<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Str;

class SchoolClass extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'classes';

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'name',
        'slug',
        'stage_id',
        'image',
        'description',
        'meta_title',
        'meta_description',
        'meta_keywords',
        'og_image',
        'order',
        'is_active',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'is_active' => 'boolean',
        'order' => 'integer',
        'stage_id' => 'integer',
    ];

    protected static function boot()
    {
        parent::boot();

        static::creating(function (SchoolClass $class) {
            if (empty($class->slug)) {
                $class->slug = Str::slug($class->name . '-' . ($class->stage_id ?? ''));
            }
        });

        static::updating(function (SchoolClass $class) {
            if (empty($class->slug)) {
                $class->slug = Str::slug($class->name . '-' . ($class->stage_id ?? ''));
            }
        });
    }

    /**
     * العلاقة مع المرحلة الدراسية.
     */
    public function stage()
    {
        return $this->belongsTo(Stage::class, 'stage_id');
    }

    /**
     * العلاقة مع المواد الدراسية.
     */
    public function subjects()
    {
        return $this->hasMany(Subject::class, 'class_id');
    }

    /**
     * نطاق الصفوف النشطة فقط.
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    /**
     * نطاق ترتيب الصفوف.
     */
    public function scopeOrdered($query)
    {
        return $query->orderBy('order', 'asc')->orderBy('name', 'asc');
    }

    /**
     * نطاق البحث في الصفوف.
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
     * نطاق الفلترة حسب المرحلة.
     */
    public function scopeByStage($query, $stageId)
    {
        if (! $stageId) {
            return $query;
        }

        return $query->where('stage_id', $stageId);
    }
}

