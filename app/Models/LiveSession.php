<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Carbon\Carbon;

class LiveSession extends Model
{
    use HasFactory;

    protected $fillable = [
        'sessionable_type',
        'sessionable_id',
        'title',
        'description',
        'scheduled_at',
        'duration_minutes',
        'timezone',
        'status',
        'created_by',
    ];

    protected $casts = [
        'scheduled_at' => 'datetime',
        'duration_minutes' => 'integer',
    ];

    /**
     * Polymorphic relation to Subject or Lesson
     */
    public function sessionable()
    {
        return $this->morphTo();
    }

    /**
     * Relation to Zoom Meeting
     */
    public function zoomMeeting()
    {
        return $this->hasOne(ZoomMeeting::class, 'live_session_id');
    }

    /**
     * Relation to Join Tokens
     */
    public function joinTokens()
    {
        return $this->hasMany(ZoomJoinToken::class, 'live_session_id');
    }

    /**
     * Relation to Attendance Logs
     */
    public function attendanceLogs()
    {
        return $this->hasMany(AttendanceLog::class, 'live_session_id');
    }

    /**
     * Relation to Creator
     */
    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    /**
     * Scopes
     */
    public function scopeScheduled($query)
    {
        return $query->where('status', 'scheduled');
    }

    public function scopeLive($query)
    {
        return $query->where('status', 'live');
    }

    public function scopeCompleted($query)
    {
        return $query->where('status', 'completed');
    }

    public function scopeUpcoming($query)
    {
        return $query->where('scheduled_at', '>', now())
            ->where('status', '!=', 'cancelled');
    }

    /**
     * Check if user can join this session
     */
    public function canJoin(User $user): bool
    {
        // Check if user is enrolled in the subject/lesson
        if ($this->sessionable_type === Subject::class) {
            $subject = $this->sessionable;
            if (!$subject) {
                return false;
            }
            return $subject->students()
                ->where('users.id', $user->id)
                ->where('enrollments.status', 'active')
                ->exists();
        } elseif ($this->sessionable_type === Lesson::class) {
            $lesson = $this->sessionable;
            if (!$lesson || !$lesson->unit || !$lesson->unit->section) {
                return false;
            }
            $subject = $lesson->unit->section->subject;
            if (!$subject) {
                return false;
            }
            return $subject->students()
                ->where('users.id', $user->id)
                ->where('enrollments.status', 'active')
                ->exists();
        }

        return false;
    }

    /**
     * Check if current time is within join window
     */
    public function isWithinTimeWindow(?int $beforeMinutes = null, ?int $afterMinutes = null): bool
    {
        $beforeMinutes = $beforeMinutes ?? config('zoom.join_window_before_minutes', 10);
        $afterMinutes = $afterMinutes ?? config('zoom.join_window_after_minutes', 15);

        $windowStart = $this->scheduled_at->copy()->subMinutes($beforeMinutes);
        $windowEnd = $this->scheduled_at->copy()->addMinutes($this->duration_minutes + $afterMinutes);

        $now = now();

        return $now->gte($windowStart) && $now->lte($windowEnd);
    }

    /**
     * Get time window start
     */
    public function getTimeWindowStart(?int $beforeMinutes = null): Carbon
    {
        $beforeMinutes = $beforeMinutes ?? config('zoom.join_window_before_minutes', 10);
        return $this->scheduled_at->copy()->subMinutes($beforeMinutes);
    }

    /**
     * Get time window end
     */
    public function getTimeWindowEnd(?int $afterMinutes = null): Carbon
    {
        $afterMinutes = $afterMinutes ?? config('zoom.join_window_after_minutes', 15);
        return $this->scheduled_at->copy()->addMinutes($this->duration_minutes + $afterMinutes);
    }
}
