<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class LibraryDownload extends Model
{
    use HasFactory;

    protected $fillable = [
        'library_item_id',
        'user_id',
        'downloaded_at',
        'ip_address',
        'user_agent',
    ];

    protected $casts = [
        'downloaded_at' => 'datetime',
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
     * نطاق التحميلات الحديثة
     */
    public function scopeRecent($query, int $days = 30)
    {
        return $query->where('downloaded_at', '>=', now()->subDays($days));
    }

    /**
     * نطاق التحميلات لمستخدم معين
     */
    public function scopeByUser($query, int $userId)
    {
        return $query->where('user_id', $userId);
    }

    /**
     * نطاق التحميلات لعنصر معين
     */
    public function scopeByItem($query, int $itemId)
    {
        return $query->where('library_item_id', $itemId);
    }
}
