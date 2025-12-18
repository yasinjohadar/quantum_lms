<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Services\SessionActivityService;
use App\Models\UserSession;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;

class SessionActivityController extends Controller
{
    protected $activityService;

    public function __construct(SessionActivityService $activityService)
    {
        $this->activityService = $activityService;
        $this->middleware('auth');
    }

    /**
     * تسجيل نشاط جديد
     */
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'session_id' => 'required|exists:user_sessions,id',
            'activity_type' => 'required|in:session_start,session_end,page_view,action,disconnect,reconnect,idle_start,idle_end,focus_lost,focus_gained',
            'page_url' => 'nullable|url|max:2048',
            'activity_details' => 'nullable|array',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors(),
            ], 422);
        }

        try {
            $session = UserSession::findOrFail($request->session_id);
            
            // التحقق من أن الجلسة تخص المستخدم الحالي
            if ($session->user_id !== Auth::id()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Unauthorized',
                ], 403);
            }

            $activity = $this->activityService->logActivity(
                $request->session_id,
                $request->activity_type,
                $request,
                $request->activity_details,
                $request->page_url
            );

            return response()->json([
                'success' => true,
                'data' => $activity,
            ], 201);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error logging activity: ' . $e->getMessage(),
            ], 500);
        }
    }
}

