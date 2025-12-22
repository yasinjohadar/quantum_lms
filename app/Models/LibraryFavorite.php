<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class LibraryFavorite extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'library_item_id',
    ];

    /**
     * العلاقة مع المستخدم
     */
    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    /**
     * العلاقة مع عنصر المكتبة
     */
    public function item()
    {
        return $this->belongsTo(LibraryItem::class, 'library_item_id');
    }
}
