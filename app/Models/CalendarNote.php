<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Carbon\Carbon;

class CalendarNote extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'user_id',
        'note_date',
        'title',
        'content',
        'color',
        'is_pinned',
    ];

    protected $casts = [
        'note_date' => 'date',
        'is_pinned' => 'boolean',
    ];

    /**
     * العلاقة مع المستخدم
     */
    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    /**
     * نطاق الملاحظات المثبتة
     */
    public function scopePinned($query)
    {
        return $query->where('is_pinned', true);
    }

    /**
     * نطاق الملاحظات حسب التاريخ
     */
    public function scopeByDate($query, Carbon $date)
    {
        return $query->whereDate('note_date', $date->toDateString());
    }

    /**
     * نطاق الملاحظات حسب النطاق الزمني
     */
    public function scopeByDateRange($query, Carbon $startDate, Carbon $endDate)
    {
        return $query->whereBetween('note_date', [$startDate->toDateString(), $endDate->toDateString()]);
    }

    /**
     * نطاق ملاحظات المستخدم
     */
    public function scopeForUser($query, int $userId)
    {
        return $query->where('user_id', $userId);
    }
}
