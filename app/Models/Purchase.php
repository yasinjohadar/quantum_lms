<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Purchase extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'user_id',
        'purchasable_type',
        'purchasable_id',
        'purchase_type',
        'price',
        'status',
        'cancelled_at',
        'cancelled_by',
        'purchased_at',
        'expires_at',
        'access_revoked_at',
        'notes',
    ];

    protected $casts = [
        'price' => 'decimal:2',
        'purchased_at' => 'datetime',
        'expires_at' => 'datetime',
        'access_revoked_at' => 'datetime',
        'cancelled_at' => 'datetime',
    ];

    /**
     * العلاقة مع المستخدم
     */
    public function user()
    {
        return $this->belongsTo(User::class)->withTrashed();
    }

    /**
     * العلاقة مع الصف أو المادة (morph)
     * مع دعم العناصر المحذوفة ناعمياً
     */
    public function purchasable()
    {
        return $this->morphTo()->withTrashed();
    }

    /**
     * العلاقة مع الدفع
     */
    public function payment()
    {
        return $this->hasOne(Payment::class);
    }

    /**
     * Scopes
     */
    public function scopePending($query)
    {
        return $query->where('status', 'pending');
    }

    /**
     * شراء معلّق وتم رفع إيصال/دفع بانتظار مراجعة الإدارة (وليس مسودة قبل الدفع).
     */
    public function scopeAwaitingAdminReview($query)
    {
        return $query->where('status', 'pending')
            ->whereHas('payment', fn ($q) => $q->where('status', 'pending'));
    }

    public function isAwaitingAdminReview(): bool
    {
        if ($this->status !== 'pending') {
            return false;
        }

        $this->loadMissing('payment');

        return $this->payment !== null && $this->payment->status === 'pending';
    }

    /**
     * طلب انضمام/شراء معلّق بدون دفعة مرفوعة؛ يحتاج مراجعة الإدارة مباشرة.
     */
    public function scopePendingDirectApproval($query)
    {
        return $query->where('status', 'pending')
            ->whereDoesntHave('payment');
    }

    public function isPendingDirectApproval(): bool
    {
        if ($this->status !== 'pending') {
            return false;
        }

        $this->loadMissing('payment');

        return $this->payment === null;
    }

    public function scopeCompleted($query)
    {
        return $query->where('status', 'completed');
    }

    public function scopeCancelled($query)
    {
        return $query->where('status', 'cancelled');
    }

    public function scopeRefunded($query)
    {
        return $query->where('status', 'refunded');
    }

    public function scopeExpiredAccessDue($query)
    {
        return $query->completed()
            ->whereNotNull('expires_at')
            ->where('expires_at', '<=', now())
            ->whereNull('access_revoked_at');
    }

    public function scopeForUser($query, $userId)
    {
        return $query->where('user_id', $userId);
    }

    public function scopeForClass($query, $classId)
    {
        return $query->where('purchasable_type', SchoolClass::class)
                    ->where('purchasable_id', $classId);
    }

    public function scopeForSubject($query, $subjectId)
    {
        return $query->where('purchasable_type', Subject::class)
                    ->where('purchasable_id', $subjectId);
    }
}
