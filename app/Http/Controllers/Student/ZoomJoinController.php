<?php

namespace App\Http\Controllers\Student;

use App\DTOs\Zoom\JoinTokenDataDTO;
use App\Events\StudentJoinedSession;
use App\Events\StudentLeftSession;
use App\Http\Controllers\Controller;
use App\Http\Requests\Student\GetJoinTokenRequest;
use App\Models\AttendanceLog;
use App\Models\LiveSession;
use App\Services\Zoom\AttendanceService;
use App\Services\Zoom\JoinTokenService;
use App\Services\Zoom\ZoomService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\View\View;

class ZoomJoinController extends Controller
{
    protected ZoomService $zoomService;
    protected JoinTokenService $tokenService;
    protected AttendanceService $attendanceService;

    public function __construct(
        ZoomService $zoomService,
        JoinTokenService $tokenService,
        AttendanceService $attendanceService
    ) {
        $this->zoomService = $zoomService;
        $this->tokenService = $tokenService;
        $this->attendanceService = $attendanceService;
    }

    /**
     * Get join token and SDK data
     */
    public function getJoinToken(GetJoinTokenRequest $request, LiveSession $liveSession): JsonResponse
    {
        $user = Auth::user();

        // Check authorization
        $this->authorize('join', $liveSession);

        // Validate join request
        $validation = $this->zoomService->validateJoinRequest($user, $liveSession);
        if (!$validation['valid']) {
            return response()->json([
                'success' => false,
                'errors' => $validation['errors'],
            ], 403);
        }

        // Check for duplicate active join
        $activeAttendance = AttendanceLog::where('user_id', $user->id)
            ->where('live_session_id', $liveSession->id)
            ->whereNull('left_at')
            ->first();

        if ($activeAttendance) {
            return response()->json([
                'success' => false,
                'message' => 'You are already in this session.',
            ], 409);
        }

        try {
            $zoomMeeting = $liveSession->zoomMeeting;

            if (!$zoomMeeting) {
                return response()->json([
                    'success' => false,
                    'message' => 'Zoom meeting not found.',
                ], 404);
            }

            // Create join token
            $token = $this->tokenService->createToken($user, $liveSession, $request);

            // Get raw token from session
            $rawToken = $request->session()->get("zoom_token_{$token->id}");

            // Generate signature
            $signature = $this->zoomService->generateJoinSignature(
                $zoomMeeting->zoom_meeting_id,
                '0', // participant role
                3600 // 1 hour expiry
            );

            // Create DTO
            $joinData = new JoinTokenDataDTO(
                meetingNumber: $zoomMeeting->zoom_meeting_id,
                userName: $user->name,
                userEmail: $user->email,
                signature: $signature,
                passcode: $zoomMeeting->passcode,
                role: 0,
                sdkKey: config('zoom.sdk_key')
            );

            return response()->json([
                'success' => true,
                'data' => $joinData->toArray(),
                'token' => $rawToken, // One-time use token
            ]);
        } catch (\Exception $e) {
            Log::error('Failed to generate join token: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Failed to generate join token.',
            ], 500);
        }
    }

    /**
     * Serve join page
     */
    public function join(Request $request, LiveSession $liveSession): View
    {
        $user = Auth::user();

        // Check authorization
        $this->authorize('join', $liveSession);

        $zoomMeeting = $liveSession->zoomMeeting;

        if (!$zoomMeeting) {
            abort(404, 'Zoom meeting not found');
        }

        return view('student.live-sessions.join', compact('liveSession', 'zoomMeeting', 'user'));
    }

    /**
     * Handle join event (called from frontend)
     */
    public function onJoin(Request $request, LiveSession $liveSession): JsonResponse
    {
        $user = Auth::user();

        try {
            // Log attendance
            $attendanceLog = $this->attendanceService->logJoin($user, $liveSession, $request);

            // Fire event
            event(new StudentJoinedSession($user, $liveSession, $attendanceLog));

            return response()->json([
                'success' => true,
                'message' => 'Joined successfully.',
            ]);
        } catch (\Exception $e) {
            Log::error('Failed to log join: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Failed to log join.',
            ], 500);
        }
    }

    /**
     * Handle leave event (called from frontend)
     */
    public function onLeave(Request $request, LiveSession $liveSession): JsonResponse
    {
        $user = Auth::user();

        try {
            // Find active attendance log
            $attendanceLog = AttendanceLog::where('user_id', $user->id)
                ->where('live_session_id', $liveSession->id)
                ->whereNull('left_at')
                ->first();

            if ($attendanceLog) {
                $this->attendanceService->logLeave($attendanceLog, $request);

                // Fire event
                event(new StudentLeftSession($user, $liveSession, $attendanceLog));
            }

            return response()->json([
                'success' => true,
                'message' => 'Left successfully.',
            ]);
        } catch (\Exception $e) {
            Log::error('Failed to log leave: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Failed to log leave.',
            ], 500);
        }
    }
}
