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
        'purchased_at',
        'expires_at',
        'notes',
    ];

    protected $casts = [
        'price' => 'decimal:2',
        'purchased_at' => 'datetime',
        'expires_at' => 'datetime',
    ];

    /**
     * العلاقة مع المستخدم
     */
    public function user()
    {
        return $this->belongsTo(User::class);
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
