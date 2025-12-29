<?php

namespace App\Services\Zoom;

use App\Models\AttendanceLog;
use App\Models\LiveSession;
use App\Models\Subject;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Maatwebsite\Excel\Facades\Excel;
use Symfony\Component\HttpFoundation\Response;

class AttendanceService
{
    /**
     * Log student join
     */
    public function logJoin(User $user, LiveSession $session, Request $request): AttendanceLog
    {
        // Check if there's an active attendance log
        $activeLog = AttendanceLog::where('user_id', $user->id)
            ->where('live_session_id', $session->id)
            ->whereNull('left_at')
            ->first();

        if ($activeLog) {
            return $activeLog;
        }

        $zoomMeetingId = $session->zoomMeeting?->zoom_meeting_id;

        return AttendanceLog::create([
            'user_id' => $user->id,
            'live_session_id' => $session->id,
            'zoom_meeting_id' => $zoomMeetingId,
            'joined_at' => now(),
            'join_ip' => $request->ip(),
            'user_agent' => $request->userAgent(),
        ]);
    }

    /**
     * Log student leave
     */
    public function logLeave(AttendanceLog $log, Request $request): AttendanceLog
    {
        if ($log->left_at) {
            return $log; // Already logged
        }

        $log->update([
            'left_at' => now(),
        ]);

        $log->calculateDuration();

        return $log->fresh();
    }

    /**
     * Get session attendance
     */
    public function getSessionAttendance(LiveSession $session): Collection
    {
        return AttendanceLog::where('live_session_id', $session->id)
            ->with('user')
            ->orderBy('joined_at', 'desc')
            ->get();
    }

    /**
     * Get user attendance
     */
    public function getUserAttendance(User $user, ?LiveSession $session = null): Collection
    {
        $query = AttendanceLog::where('user_id', $user->id)
            ->with('liveSession');

        if ($session) {
            $query->where('live_session_id', $session->id);
        }

        return $query->orderBy('joined_at', 'desc')->get();
    }

    /**
     * Get session attendance statistics
     */
    public function getSessionAttendanceStats(LiveSession $session): array
    {
        // Get enrolled students count
        $enrolledCount = 0;
        if ($session->sessionable_type === \App\Models\Subject::class) {
            $subject = $session->sessionable;
            $enrolledCount = $subject->students()
                ->where('enrollments.status', 'active')
                ->count();
        } elseif ($session->sessionable_type === \App\Models\Lesson::class) {
            $lesson = $session->sessionable;
            $subject = $lesson->unit->section->subject;
            $enrolledCount = $subject->students()
                ->where('enrollments.status', 'active')
                ->count();
        }

        // Get attendance logs
        $attendanceLogs = AttendanceLog::where('live_session_id', $session->id)->get();
        $attendedCount = $attendanceLogs->unique('user_id')->count();
        $attendancePercentage = $enrolledCount > 0 ? ($attendedCount / $enrolledCount) * 100 : 0;

        // Calculate average duration
        $totalDuration = $attendanceLogs->sum('duration_seconds');
        $avgDuration = $attendedCount > 0 ? $totalDuration / $attendedCount : 0;

        return [
            'total_enrolled' => $enrolledCount,
            'attended_count' => $attendedCount,
            'absent_count' => $enrolledCount - $attendedCount,
            'attendance_percentage' => round($attendancePercentage, 2),
            'average_duration_seconds' => round($avgDuration),
            'average_duration_formatted' => $this->formatDuration(round($avgDuration)),
        ];
    }

    /**
     * Get user attendance statistics
     */
    public function getUserAttendanceStats(User $user, ?Subject $subject = null): array
    {
        $query = AttendanceLog::where('user_id', $user->id)
            ->with('liveSession');

        if ($subject) {
            $query->whereHas('liveSession', function ($q) use ($subject) {
                $q->where('sessionable_type', Subject::class)
                  ->where('sessionable_id', $subject->id);
            });
        }

        $logs = $query->get();
        $totalSessions = $logs->unique('live_session_id')->count();
        $totalTime = $logs->sum('duration_seconds');
        $avgTime = $totalSessions > 0 ? $totalTime / $totalSessions : 0;

        return [
            'total_sessions_attended' => $totalSessions,
            'total_time_seconds' => $totalTime,
            'total_time_formatted' => $this->formatDuration($totalTime),
            'average_time_seconds' => round($avgTime),
            'average_time_formatted' => $this->formatDuration(round($avgTime)),
        ];
    }

    /**
     * Export session attendance
     */
    public function exportSessionAttendance(LiveSession $session, string $format = 'excel'): Response
    {
        $attendance = $this->getSessionAttendance($session);

        $data = $attendance->map(function ($log) {
            return [
                'Student Name' => $log->user->name,
                'Email' => $log->user->email,
                'Joined At' => $log->joined_at->format('Y-m-d H:i:s'),
                'Left At' => $log->left_at?->format('Y-m-d H:i:s') ?? 'Still Active',
                'Duration' => $log->formatted_duration ?? 'N/A',
                'IP Address' => $log->join_ip,
            ];
        })->toArray();

        if ($format === 'excel') {
            $headings = count($data) > 0 ? array_keys($data[0]) : [];
            return Excel::download(
                new \App\Exports\SimpleArrayExport($data, $headings),
                "attendance_{$session->id}_" . now()->format('Y-m-d') . '.xlsx'
            );
        }

        // PDF export would require additional package
        throw new \Exception('PDF export not implemented');
    }

    /**
     * Format duration in seconds to readable format
     */
    protected function formatDuration(int $seconds): string
    {
        $hours = floor($seconds / 3600);
        $minutes = floor(($seconds % 3600) / 60);
        $secs = $seconds % 60;

        if ($hours > 0) {
            return sprintf('%d:%02d:%02d', $hours, $minutes, $secs);
        }

        return sprintf('%d:%02d', $minutes, $secs);
    }
}

