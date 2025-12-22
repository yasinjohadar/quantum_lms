<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Str;

class LibraryCategory extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'name',
        'slug',
        'description',
        'icon',
        'color',
        'order',
        'is_active',
    ];

    protected $casts = [
        'order' => 'integer',
        'is_active' => 'boolean',
    ];

    protected static function boot()
    {
        parent::boot();

        static::creating(function (LibraryCategory $category) {
            if (empty($category->slug)) {
                $category->slug = Str::slug($category->name);
            }
        });

        static::updating(function (LibraryCategory $category) {
            if ($category->isDirty('name') && empty($category->slug)) {
                $category->slug = Str::slug($category->name);
            }
        });
    }

    /**
     * العلاقة مع عناصر المكتبة
     */
    public function items()
    {
        return $this->hasMany(LibraryItem::class, 'category_id');
    }

    /**
     * نطاق التصنيفات النشطة
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    /**
     * نطاق ترتيب التصنيفات
     */
    public function scopeOrdered($query)
    {
        return $query->orderBy('order', 'asc')->orderBy('name', 'asc');
    }

    /**
     * الحصول على عدد العناصر في التصنيف
     */
    public function getItemsCount(): int
    {
        return $this->items()->count();
    }

    /**
     * الحصول على إجمالي التحميلات للتصنيف
     */
    public function getTotalDownloads(): int
    {
        return $this->items()->sum('download_count');
    }
}
