<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\MorphTo;

class ReviewComment extends Model
{
    use HasFactory;

    protected $fillable = [
        'reviewable_type',
        'reviewable_id',
        'user_id',
        'parent_id',
        'comment',
        'is_resolved',
        'resolved_at',
    ];

    protected $casts = [
        'is_resolved' => 'boolean',
        'resolved_at' => 'datetime',
    ];

    /**
     * العلاقة مع العنصر المرتبط (Polymorphic)
     */
    public function reviewable(): MorphTo
    {
        return $this->morphTo();
    }

    /**
     * العلاقة مع المستخدم الذي كتب الملاحظة
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * العلاقة مع الملاحظة الرئيسية (للردود)
     */
    public function parent(): BelongsTo
    {
        return $this->belongsTo(ReviewComment::class, 'parent_id');
    }

    /**
     * العلاقة مع الردود على هذه الملاحظة
     */
    public function replies(): HasMany
    {
        return $this->hasMany(ReviewComment::class, 'parent_id')->orderBy('created_at', 'asc');
    }

    /**
     * Scope للملاحظات الرئيسية (بدون parent_id)
     */
    public function scopeMainComments($query)
    {
        return $query->whereNull('parent_id');
    }

    /**
     * Scope للملاحظات المحلولة
     */
    public function scopeResolved($query)
    {
        return $query->where('is_resolved', true);
    }

    /**
     * Scope للملاحظات غير المحلولة
     */
    public function scopeUnresolved($query)
    {
        return $query->where('is_resolved', false);
    }

    /**
     * Scope للملاحظات الخاصة بعنصر معين
     */
    public function scopeForReviewable($query, $reviewableType, $reviewableId)
    {
        return $query->where('reviewable_type', $reviewableType)
                    ->where('reviewable_id', $reviewableId);
    }

    /**
     * حل الملاحظة
     */
    public function resolve(): void
    {
        $this->update([
            'is_resolved' => true,
            'resolved_at' => now(),
        ]);
    }

    /**
     * إلغاء حل الملاحظة
     */
    public function unresolve(): void
    {
        $this->update([
            'is_resolved' => false,
            'resolved_at' => null,
        ]);
    }

    /**
     * التحقق من أن الملاحظة محلولة
     */
    public function isResolved(): bool
    {
        return $this->is_resolved;
    }

    /**
     * التحقق من أن الملاحظة هي ملاحظة رئيسية (ليست رد)
     */
    public function isMainComment(): bool
    {
        return is_null($this->parent_id);
    }

    /**
     * التحقق من أن الملاحظة هي رد
     */
    public function isReply(): bool
    {
        return !is_null($this->parent_id);
    }
}
