<?php

namespace App\Http\Controllers\Student;

use App\Http\Controllers\Controller;
use App\Models\LiveSession;
use App\Models\Subject;
use App\Services\Zoom\AttendanceService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class AttendanceController extends Controller
{
    protected AttendanceService $attendanceService;

    public function __construct(AttendanceService $attendanceService)
    {
        $this->attendanceService = $attendanceService;
    }

    /**
     * Show student's attendance history
     */
    public function index(Request $request): View
    {
        $user = Auth::user();
        $attendance = $this->attendanceService->getUserAttendance($user);
        $stats = $this->attendanceService->getUserAttendanceStats($user);

        return view('student.attendance.index', compact('attendance', 'stats'));
    }

    /**
     * Show attendance details for specific session
     */
    public function show(Request $request, LiveSession $liveSession): View
    {
        $user = Auth::user();
        $this->authorize('view', $liveSession);

        $attendanceLog = $this->attendanceService->getUserAttendance($user, $liveSession)->first();

        if (!$attendanceLog) {
            abort(404, 'Attendance record not found');
        }

        return view('student.attendance.show', compact('liveSession', 'attendanceLog'));
    }

    /**
     * Get student attendance statistics
     */
    public function stats(Request $request, ?Subject $subject = null): JsonResponse
    {
        $user = Auth::user();
        $stats = $this->attendanceService->getUserAttendanceStats($user, $subject);

        return response()->json([
            'success' => true,
            'data' => $stats,
        ]);
    }
}
