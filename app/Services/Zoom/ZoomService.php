<?php

namespace App\Services\Zoom;

use App\Models\LiveSession;
use App\Models\User;
use App\Models\ZoomAccount;
use App\Models\ZoomMeeting;
use Carbon\Carbon;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\Log;

class ZoomService
{
    protected ZoomClient $client;
    protected ?ZoomAccount $account;

    public function __construct(?ZoomAccount $account = null)
    {
        $this->account = $account ?? ZoomAccount::getDefault();
        $this->client = new ZoomClient($this->account);
    }

    /**
     * Create Zoom meeting for a Live Session
     */
    public function createMeetingForSession(LiveSession $session, User $creator): ZoomMeeting
    {
        $defaultSettings = config('zoom.default_meeting_settings', []);

        $meetingData = [
            'topic' => $session->title,
            'type' => 2, // Scheduled meeting
            'start_time' => $session->scheduled_at->format('Y-m-d\TH:i:s\Z'),
            'duration' => $session->duration_minutes,
            'timezone' => $session->timezone,
            'settings' => array_merge($defaultSettings, [
                'waiting_room' => true,
                'join_before_host' => false,
            ]),
        ];

        try {
            $zoomResponse = $this->client->createMeeting($meetingData);

            $zoomMeeting = ZoomMeeting::create([
                'live_session_id' => $session->id,
                'zoom_meeting_id' => (string) $zoomResponse['id'],
                'zoom_uuid' => $zoomResponse['uuid'],
                'host_email' => $zoomResponse['host_email'] ?? $creator->email,
                'host_id' => $zoomResponse['host_id'] ?? null,
                'topic' => $zoomResponse['topic'],
                'start_time' => Carbon::parse($zoomResponse['start_time']),
                'duration' => $zoomResponse['duration'],
                'timezone' => $zoomResponse['timezone'],
                'encrypted_passcode' => isset($zoomResponse['password']) ? Crypt::encryptString($zoomResponse['password']) : null,
                'settings_json' => $zoomResponse['settings'] ?? [],
                'status' => 'created',
                'created_by' => $creator->id,
            ]);

            return $zoomMeeting;
        } catch (\Exception $e) {
            Log::error('Failed to create Zoom meeting for session: ' . $e->getMessage());
            throw $e;
        }
    }

    /**
     * Update Zoom meeting
     */
    public function updateMeeting(ZoomMeeting $meeting, array $data): ZoomMeeting
    {
        $updateData = [];

        if (isset($data['topic'])) {
            $updateData['topic'] = $data['topic'];
        }

        if (isset($data['start_time'])) {
            $updateData['start_time'] = Carbon::parse($data['start_time'])->format('Y-m-d\TH:i:s\Z');
        }

        if (isset($data['duration'])) {
            $updateData['duration'] = $data['duration'];
        }

        if (isset($data['timezone'])) {
            $updateData['timezone'] = $data['timezone'];
        }

        if (isset($data['settings'])) {
            $updateData['settings'] = $data['settings'];
        }

        try {
            $zoomResponse = $this->client->updateMeeting($meeting->zoom_meeting_id, $updateData);

            $meeting->update([
                'topic' => $zoomResponse['topic'] ?? $meeting->topic,
                'start_time' => isset($zoomResponse['start_time']) ? Carbon::parse($zoomResponse['start_time']) : $meeting->start_time,
                'duration' => $zoomResponse['duration'] ?? $meeting->duration,
                'timezone' => $zoomResponse['timezone'] ?? $meeting->timezone,
                'settings_json' => $zoomResponse['settings'] ?? $meeting->settings_json,
            ]);

            return $meeting->fresh();
        } catch (\Exception $e) {
            Log::error('Failed to update Zoom meeting: ' . $e->getMessage());
            throw $e;
        }
    }

    /**
     * Cancel Zoom meeting
     */
    public function cancelMeeting(ZoomMeeting $meeting): bool
    {
        try {
            $this->client->deleteMeeting($meeting->zoom_meeting_id);

            $meeting->update([
                'status' => 'cancelled',
            ]);

            return true;
        } catch (\Exception $e) {
            Log::error('Failed to cancel Zoom meeting: ' . $e->getMessage());
            throw $e;
        }
    }

    /**
     * Sync meeting status from Zoom
     */
    public function syncMeetingStatus(ZoomMeeting $meeting): ZoomMeeting
    {
        try {
            $zoomResponse = $this->client->getMeeting($meeting->zoom_meeting_id);

            $meeting->syncFromZoom($zoomResponse);

            return $meeting->fresh();
        } catch (\Exception $e) {
            Log::error('Failed to sync Zoom meeting status: ' . $e->getMessage());
            throw $e;
        }
    }

    /**
     * Generate join signature for Zoom Meeting SDK
     */
    public function generateJoinSignature(string $meetingNumber, string $role, int $expiresIn = 3600): string
    {
        // Use account SDK credentials if available, otherwise fallback to config
        if ($this->account && $this->account->sdk_key && $this->account->sdk_secret) {
            $sdkKey = $this->account->sdk_key;
            $sdkSecret = $this->account->decrypted_sdk_secret;
        } else {
            $sdkKey = config('zoom.sdk_key');
            $sdkSecret = config('zoom.sdk_secret');
        }

        if (!$sdkKey || !$sdkSecret) {
            throw new \Exception('Zoom SDK credentials not configured');
        }

        $timestamp = time() * 1000; // milliseconds
        $expire = $timestamp + ($expiresIn * 1000);

        $data = base64_encode($sdkKey . $meetingNumber . $expire . $role);

        $hash = hash_hmac('sha256', $data, $sdkSecret, true);
        $signature = base64_encode($hash);

        return $sdkKey . '.' . $meetingNumber . '.' . $expire . '.' . $role . '.' . $signature;
    }

    /**
     * Validate join request
     */
    public function validateJoinRequest(User $user, LiveSession $session): array
    {
        $errors = [];

        // Check if user is enrolled
        if (!$session->canJoin($user)) {
            $errors[] = 'You are not enrolled in this session';
        }

        // Check if user is active
        if (!$user->is_active) {
            $errors[] = 'Your account is inactive';
        }

        // Check time window
        if (!$session->isWithinTimeWindow()) {
            $errors[] = 'Session is not available for joining at this time';
        }

        // Check if session is cancelled
        if ($session->status === 'cancelled') {
            $errors[] = 'This session has been cancelled';
        }

        return [
            'valid' => empty($errors),
            'errors' => $errors,
        ];
    }
}

