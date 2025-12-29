<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Crypt;

class ZoomMeeting extends Model
{
    use HasFactory;

    protected $fillable = [
        'live_session_id',
        'zoom_meeting_id',
        'zoom_uuid',
        'host_email',
        'host_id',
        'topic',
        'start_time',
        'duration',
        'timezone',
        'encrypted_passcode',
        'settings_json',
        'status',
        'created_by',
    ];

    protected $casts = [
        'start_time' => 'datetime',
        'duration' => 'integer',
        'settings_json' => 'array',
    ];

    /**
     * Relation to Live Session
     */
    public function liveSession()
    {
        return $this->belongsTo(LiveSession::class, 'live_session_id');
    }

    /**
     * Relation to Creator
     */
    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    /**
     * Get decrypted passcode
     */
    public function getPasscodeAttribute(): ?string
    {
        if (!$this->encrypted_passcode) {
            return null;
        }

        try {
            return Crypt::decryptString($this->encrypted_passcode);
        } catch (\Exception $e) {
            return null;
        }
    }

    /**
     * Set encrypted passcode
     */
    public function setPasscodeAttribute(?string $value): void
    {
        if ($value) {
            $this->attributes['encrypted_passcode'] = Crypt::encryptString($value);
        } else {
            $this->attributes['encrypted_passcode'] = null;
        }
    }

    /**
     * Check if meeting is active
     */
    public function isActive(): bool
    {
        return in_array($this->status, ['created', 'started']);
    }

    /**
     * Check if meeting can start
     */
    public function canStart(): bool
    {
        return $this->status === 'created' && now()->gte($this->start_time);
    }

    /**
     * Sync meeting data from Zoom API
     */
    public function syncFromZoom(array $zoomData): self
    {
        $this->update([
            'topic' => $zoomData['topic'] ?? $this->topic,
            'start_time' => $zoomData['start_time'] ?? $this->start_time,
            'duration' => $zoomData['duration'] ?? $this->duration,
            'status' => $this->determineStatus($zoomData),
            'settings_json' => $zoomData['settings'] ?? $this->settings_json,
        ]);

        return $this;
    }

    /**
     * Determine status from Zoom data
     */
    protected function determineStatus(array $zoomData): string
    {
        if (isset($zoomData['status'])) {
            return match($zoomData['status']) {
                'waiting' => 'created',
                'started' => 'started',
                'finished' => 'ended',
                default => $this->status,
            };
        }

        return $this->status;
    }
}
