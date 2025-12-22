<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class LibraryView extends Model
{
    use HasFactory;

    protected $fillable = [
        'library_item_id',
        'user_id',
        'viewed_at',
        'ip_address',
        'view_duration',
    ];

    protected $casts = [
        'viewed_at' => 'datetime',
        'view_duration' => 'integer',
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
     * نطاق المشاهدات الحديثة
     */
    public function scopeRecent($query, int $days = 30)
    {
        return $query->where('viewed_at', '>=', now()->subDays($days));
    }

    /**
     * نطاق المشاهدات لمستخدم معين
     */
    public function scopeByUser($query, int $userId)
    {
        return $query->where('user_id', $userId);
    }

    /**
     * نطاق المشاهدات لعنصر معين
     */
    public function scopeByItem($query, int $itemId)
    {
        return $query->where('library_item_id', $itemId);
    }
}
