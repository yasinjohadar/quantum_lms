<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class LibraryRating extends Model
{
    use HasFactory;

    protected $fillable = [
        'library_item_id',
        'user_id',
        'rating',
        'comment',
    ];

    protected $casts = [
        'rating' => 'integer',
    ];

    /**
     * العلاقة مع عنصر المكتبة
     */
    public function item()
    {
        return $this->belongsTo(LibraryItem::class, 'library_item_id');
    }

    /**
     * العلاقة مع المستخدم
     */
    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    /**
     * تحديث تقييم العنصر بعد الحفظ
     */
    protected static function boot()
    {
        parent::boot();

        static::saved(function (LibraryRating $rating) {
            $rating->item->calculateAverageRating();
        });

        static::deleted(function (LibraryRating $rating) {
            $rating->item->calculateAverageRating();
        });
    }
}
