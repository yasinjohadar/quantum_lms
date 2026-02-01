<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PlatformReview extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'class_id',
        'stars',
        'comment',
        'status',
        'photo',
        'order',
        'approved_at',
        'approved_by',
    ];

    protected $casts = [
        'user_id' => 'integer',
        'class_id' => 'integer',
        'stars' => 'integer',
        'order' => 'integer',
        'approved_at' => 'datetime',
        'approved_by' => 'integer',
    ];

    /**
     * العلاقة مع الطالب
     */
    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    /**
     * العلاقة مع الصف المعروض
     */
    public function schoolClass()
    {
        return $this->belongsTo(SchoolClass::class, 'class_id');
    }

    /**
     * العلاقة مع من اعتمد التقييم
     */
    public function approvedByUser()
    {
        return $this->belongsTo(User::class, 'approved_by');
    }

    /**
     * نطاق المراجعات المعتمدة فقط
     */
    public function scopeApproved($query)
    {
        return $query->where('status', 'approved');
    }

    /**
     * نطاق ترتيب العرض
     */
    public function scopeOrdered($query)
    {
        return $query->orderBy('order', 'asc')->orderBy('created_at', 'desc');
    }

    /**
     * صورة الطالب المعروضة (مراجعة أو صورة المستخدم)
     */
    public function getDisplayPhotoUrlAttribute(): ?string
    {
        if ($this->photo) {
            return asset('storage/' . $this->photo);
        }
        return $this->user && $this->user->photo
            ? asset('storage/' . $this->user->photo)
            : null;
    }
}
