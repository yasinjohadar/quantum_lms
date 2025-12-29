<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Carbon\Carbon;

class AttendanceLog extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'live_session_id',
        'zoom_meeting_id',
        'joined_at',
        'left_at',
        'join_ip',
        'user_agent',
        'duration_seconds',
        'meta_json',
    ];

    protected $casts = [
        'joined_at' => 'datetime',
        'left_at' => 'datetime',
        'duration_seconds' => 'integer',
        'meta_json' => 'array',
    ];

    /**
     * Relation to User
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Relation to Live Session
     */
    public function liveSession()
    {
        return $this->belongsTo(LiveSession::class);
    }

    /**
     * Relation to Zoom Meeting
     */
    public function zoomMeeting()
    {
        return $this->belongsTo(ZoomMeeting::class, 'zoom_meeting_id', 'zoom_meeting_id');
    }

    /**
     * Calculate duration
     */
    public function calculateDuration(): ?int
    {
        if (!$this->joined_at) {
            return null;
        }

        $endTime = $this->left_at ?? now();
        $this->duration_seconds = $this->joined_at->diffInSeconds($endTime);
        $this->save();

        return $this->duration_seconds;
    }

    /**
     * Check if attendance is still active (user hasn't left)
     */
    public function isActive(): bool
    {
        return $this->joined_at && !$this->left_at;
    }

    /**
     * Get formatted duration
     */
    public function getFormattedDurationAttribute(): ?string
    {
        if (!$this->duration_seconds) {
            return null;
        }

        $hours = floor($this->duration_seconds / 3600);
        $minutes = floor(($this->duration_seconds % 3600) / 60);
        $seconds = $this->duration_seconds % 60;

        if ($hours > 0) {
            return sprintf('%d:%02d:%02d', $hours, $minutes, $seconds);
        }

        return sprintf('%d:%02d', $minutes, $seconds);
    }
}
