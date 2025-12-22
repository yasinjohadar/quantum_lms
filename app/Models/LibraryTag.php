<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class LibraryTag extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'slug',
        'color',
    ];

    protected static function boot()
    {
        parent::boot();

        static::creating(function (LibraryTag $tag) {
            if (empty($tag->slug)) {
                $tag->slug = Str::slug($tag->name);
            }
        });

        static::updating(function (LibraryTag $tag) {
            if ($tag->isDirty('name') && empty($tag->slug)) {
                $tag->slug = Str::slug($tag->name);
            }
        });
    }

    /**
     * العلاقة مع عناصر المكتبة (many-to-many)
     */
    public function items()
    {
        return $this->belongsToMany(LibraryItem::class, 'library_item_tags', 'tag_id', 'library_item_id');
    }

    /**
     * نطاق الوسوم الأكثر استخداماً
     */
    public function scopePopular($query, int $limit = 10)
    {
        return $query->withCount('items')
                    ->orderBy('items_count', 'desc')
                    ->limit($limit);
    }
}
