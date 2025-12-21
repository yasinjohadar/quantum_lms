<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Review extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'reviewable_type',
        'reviewable_id',
        'user_id',
        'rating',
        'title',
        'comment',
        'status',
        'approved_by',
        'approved_at',
        'rejected_reason',
        'is_helpful_count',
        'is_anonymous',
    ];

    protected $casts = [
        'rating' => 'integer',
        'is_helpful_count' => 'integer',
        'is_anonymous' => 'boolean',
        'approved_at' => 'datetime',
    ];

    /**
     * حالات التقييم
     */
    public const STATUS_PENDING = 'pending';
    public const STATUS_APPROVED = 'approved';
    public const STATUS_REJECTED = 'rejected';

    public const STATUSES = [
        self::STATUS_PENDING => 'قيد المراجعة',
        self::STATUS_APPROVED => 'مقبول',
        self::STATUS_REJECTED => 'مرفوض',
    ];

    /**
     * العلاقات
     */
    public function reviewable(): MorphTo
    {
        return $this->morphTo();
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function approver(): BelongsTo
    {
        return $this->belongsTo(User::class, 'approved_by');
    }

    public function votes(): HasMany
    {
        return $this->hasMany(ReviewVote::class);
    }

    /**
     * Scopes
     */
    public function scopeApproved($query)
    {
        return $query->where('status', self::STATUS_APPROVED);
    }

    public function scopePending($query)
    {
        return $query->where('status', self::STATUS_PENDING);
    }

    public function scopeRejected($query)
    {
        return $query->where('status', self::STATUS_REJECTED);
    }

    public function scopeForSubject($query, $subjectId)
    {
        return $query->where('reviewable_type', Subject::class)
            ->where('reviewable_id', $subjectId);
    }

    public function scopeForClass($query, $classId)
    {
        return $query->where('reviewable_type', SchoolClass::class)
            ->where('reviewable_id', $classId);
    }

    public function scopeWithRating($query, $rating)
    {
        return $query->where('rating', $rating);
    }

    public function scopeRecent($query)
    {
        return $query->orderBy('created_at', 'desc');
    }

    public function scopeMostHelpful($query)
    {
        return $query->orderBy('is_helpful_count', 'desc');
    }

    public function scopeHighestRated($query)
    {
        return $query->orderBy('rating', 'desc');
    }

    /**
     * Methods
     */
    public function approve(?User $approver = null): void
    {
        $this->status = self::STATUS_APPROVED;
        $this->approved_by = $approver?->id ?? auth()->id();
        $this->approved_at = now();
        $this->rejected_reason = null;
        $this->save();
    }

    public function reject(string $reason, ?User $rejector = null): void
    {
        $this->status = self::STATUS_REJECTED;
        $this->approved_by = $rejector?->id ?? auth()->id();
        $this->rejected_reason = $reason;
        $this->approved_at = null;
        $this->save();
    }

    public function isApproved(): bool
    {
        return $this->status === self::STATUS_APPROVED;
    }

    public function isPending(): bool
    {
        return $this->status === self::STATUS_PENDING;
    }

    public function isRejected(): bool
    {
        return $this->status === self::STATUS_REJECTED;
    }

    public function incrementHelpfulCount(): void
    {
        $this->increment('is_helpful_count');
    }

    public function decrementHelpfulCount(): void
    {
        $this->decrement('is_helpful_count');
    }

    /**
     * Accessors
     */
    public function getStatusNameAttribute(): string
    {
        return self::STATUSES[$this->status] ?? $this->status;
    }

    public function getDisplayNameAttribute(): string
    {
        if ($this->is_anonymous) {
            return 'مستخدم مجهول';
        }
        return $this->user->name ?? 'مستخدم محذوف';
    }
}
