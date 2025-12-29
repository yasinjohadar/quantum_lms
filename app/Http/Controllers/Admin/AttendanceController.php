<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\LiveSession;
use App\Models\User;
use App\Services\Zoom\AttendanceService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class AttendanceController extends Controller
{
    protected AttendanceService $attendanceService;

    public function __construct(AttendanceService $attendanceService)
    {
        $this->attendanceService = $attendanceService;
    }

    /**
     * Show attendance list for session
     */
    public function index(Request $request, LiveSession $liveSession): View
    {
        $this->authorize('view', $liveSession);

        $attendance = $this->attendanceService->getSessionAttendance($liveSession);
        $stats = $this->attendanceService->getSessionAttendanceStats($liveSession);

        return view('admin.live-sessions.attendance.index', compact('liveSession', 'attendance', 'stats'));
    }

    /**
     * Show specific student attendance details
     */
    public function show(Request $request, LiveSession $liveSession, User $user): View
    {
        $this->authorize('view', $liveSession);

        $attendanceLog = $this->attendanceService->getUserAttendance($user, $liveSession)->first();

        if (!$attendanceLog) {
            abort(404, 'Attendance record not found');
        }

        return view('admin.live-sessions.attendance.show', compact('liveSession', 'user', 'attendanceLog'));
    }

    /**
     * Export attendance
     */
    public function export(Request $request, LiveSession $liveSession, string $format)
    {
        $this->authorize('view', $liveSession);

        return $this->attendanceService->exportSessionAttendance($liveSession, $format);
    }

    /**
     * Get attendance statistics (JSON)
     */
    public function stats(LiveSession $liveSession): JsonResponse
    {
        $this->authorize('view', $liveSession);

        $stats = $this->attendanceService->getSessionAttendanceStats($liveSession);

        return response()->json([
            'success' => true,
            'data' => $stats,
        ]);
    }
}
