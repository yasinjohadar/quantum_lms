<?php

namespace App\Events;

use App\Models\AttendanceLog;
use App\Models\LiveSession;
use App\Models\User;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class StudentLeftSession
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    /**
     * Create a new event instance.
     */
    public function __construct(
        public User $user,
        public LiveSession $liveSession,
        public AttendanceLog $attendanceLog
    ) {
        //
    }
}
