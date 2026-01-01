<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SMSLog extends Model
{
    use HasFactory;

    protected $fillable = [
        'to',
        'message',
        'type',
        'status',
        'provider',
        'error_message',
        'sent_at',
    ];

    protected $casts = [
        'sent_at' => 'datetime',
    ];

    /**
     * Scope for sent SMS
     */
    public function scopeSent($query)
    {
        return $query->where('status', 'sent');
    }

    /**
     * Scope for failed SMS
     */
    public function scopeFailed($query)
    {
        return $query->where('status', 'failed');
    }

    /**
     * Scope for OTP type
     */
    public function scopeOTP($query)
    {
        return $query->where('type', 'otp');
    }

    /**
     * Scope for notification type
     */
    public function scopeNotification($query)
    {
        return $query->where('type', 'notification');
    }

    /**
     * Check if SMS was sent successfully
     */
    public function isSent(): bool
    {
        return $this->status === 'sent';
    }

    /**
     * Check if SMS failed
     */
    public function isFailed(): bool
    {
        return $this->status === 'failed';
    }
}
