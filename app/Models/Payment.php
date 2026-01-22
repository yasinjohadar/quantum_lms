<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Payment extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'purchase_id',
        'payment_method',
        'custom_payment_method_id',
        'amount',
        'currency',
        'status',
        'transaction_id',
        'gateway_response',
        'receipt_file',
        'reviewed_by',
        'reviewed_at',
        'review_notes',
        'payment_data',
    ];

    protected $casts = [
        'amount' => 'decimal:2',
        'gateway_response' => 'array',
        'payment_data' => 'array',
        'reviewed_at' => 'datetime',
    ];

    /**
     * العلاقة مع الشراء
     */
    public function purchase()
    {
        return $this->belongsTo(Purchase::class);
    }

    /**
     * العلاقة مع وسيلة الدفع المخصصة
     */
    public function customPaymentMethod()
    {
        return $this->belongsTo(CustomPaymentMethod::class);
    }

    /**
     * العلاقة مع من راجع الدفع
     */
    public function reviewedBy()
    {
        return $this->belongsTo(User::class, 'reviewed_by');
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

    public function scopeFailed($query)
    {
        return $query->where('status', 'failed');
    }

    public function scopeRefunded($query)
    {
        return $query->where('status', 'refunded');
    }

    public function scopeNeedsReview($query)
    {
        return $query->where('status', 'pending')
                    ->whereIn('payment_method', ['iban', 'custom'])
                    ->whereNull('reviewed_at');
    }

    public function scopeByMethod($query, $method)
    {
        return $query->where('payment_method', $method);
    }
}
