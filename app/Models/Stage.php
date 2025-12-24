<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Storage;

class Stage extends Model
{
    use HasFactory, SoftDeletes;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'name',
        'slug',
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
    ];

    protected static function boot()
    {
        parent::boot();

        static::creating(function (Stage $stage) {
            if (empty($stage->slug)) {
                $stage->slug = Str::slug($stage->name);
            }
        });

        static::updating(function (Stage $stage) {
            if (empty($stage->slug)) {
                $stage->slug = Str::slug($stage->name);
            }
        });
    }

    /**
     * العلاقة مع الصفوف المدرسية.
     */
    public function classes()
    {
        return $this->hasMany(SchoolClass::class, 'stage_id');
    }

    /**
     * نطاق المراحل النشطة فقط.
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    /**
     * نطاق ترتيب المراحل.
     */
    public function scopeOrdered($query)
    {
        return $query->orderBy('order', 'asc')->orderBy('name', 'asc');
    }

    /**
     * نطاق البحث في المراحل.
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
     * الحصول على رابط الصورة
     */
    public function getImageUrlAttribute()
    {
        if (!$this->image) {
            return asset('assets/images/media/media-22.jpg');
        }

        // استخدام Storage::url() للحصول على الرابط الصحيح
        try {
            if (Storage::disk('public')->exists($this->image)) {
                return Storage::disk('public')->url($this->image);
            }
        } catch (\Exception $e) {
            // في حالة الخطأ، استخدم asset
        }

        // إذا كان المسار يحتوي على storage/ فهو جاهز
        if (str_starts_with($this->image, 'storage/')) {
            return asset($this->image);
        }

        // إذا كان المسار نسبي فقط
        return asset('storage/' . $this->image);
    }
}


