<?php

namespace App\Http\Controllers\Student;

use App\Http\Controllers\Controller;
use App\Models\LiveSession;
use App\Models\Subject;
use App\Models\Lesson;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class LiveSessionController extends Controller
{
    /**
     * Display a listing of available live sessions for the student.
     */
    public function index(Request $request): View
    {
        $user = Auth::user();

        // Get enrolled subject IDs
        $enrolledSubjectIds = $user->subjects()
            ->where('enrollments.status', 'active')
            ->pluck('subjects.id');

        // Get enrolled lesson IDs (through subjects)
        $enrolledLessonIds = Lesson::whereHas('unit.section', function ($query) use ($enrolledSubjectIds) {
            $query->whereIn('subject_id', $enrolledSubjectIds);
        })->pluck('lessons.id');

        // Build query for available sessions
        $query = LiveSession::where(function ($q) use ($enrolledSubjectIds, $enrolledLessonIds) {
            // Sessions linked to enrolled subjects
            $q->where(function ($subQuery) use ($enrolledSubjectIds) {
                $subQuery->where('sessionable_type', Subject::class)
                    ->whereIn('sessionable_id', $enrolledSubjectIds);
            })
            // Sessions linked to enrolled lessons
            ->orWhere(function ($subQuery) use ($enrolledLessonIds) {
                $subQuery->where('sessionable_type', Lesson::class)
                    ->whereIn('sessionable_id', $enrolledLessonIds);
            });
        })
        ->where('status', '!=', 'cancelled')
        ->where('scheduled_at', '>=', now())
        ->with(['sessionable', 'zoomMeeting']);

        // Filter by subject
        if ($request->filled('subject_id')) {
            $subjectId = $request->input('subject_id');
            $query->where(function ($q) use ($subjectId) {
                // Direct subject sessions
                $q->where(function ($subQuery) use ($subjectId) {
                    $subQuery->where('sessionable_type', Subject::class)
                        ->where('sessionable_id', $subjectId);
                })
                // Lesson sessions for this subject
                ->orWhere(function ($subQuery) use ($subjectId) {
                    $subQuery->where('sessionable_type', Lesson::class)
                        ->whereIn('sessionable_id', function ($query) use ($subjectId) {
                            $query->select('lessons.id')
                                ->from('lessons')
                                ->join('units', 'lessons.unit_id', '=', 'units.id')
                                ->join('subject_sections', 'units.section_id', '=', 'subject_sections.id')
                                ->where('subject_sections.subject_id', $subjectId);
                        });
                });
            });
        }

        $sessions = $query->orderBy('scheduled_at', 'asc')->paginate(15);
        
        // Get enrolled subjects for filter dropdown
        $enrolledSubjects = $user->subjects()
            ->where('enrollments.status', 'active')
            ->with('schoolClass')
            ->get();

        return view('student.live-sessions.index', compact('sessions', 'enrolledSubjects'));
    }

    /**
     * Display the specified live session.
     */
    public function show(LiveSession $liveSession): View
    {
        $user = Auth::user();

        // Check authorization
        $this->authorize('view', $liveSession);

        // Check if user can join
        if (!$liveSession->canJoin($user)) {
            abort(403, 'You are not enrolled in this session.');
        }

        $liveSession->load(['sessionable', 'zoomMeeting', 'creator']);

        // Get user's attendance for this session
        $attendanceLog = $liveSession->attendanceLogs()
            ->where('user_id', $user->id)
            ->first();

        // Check if can join now
        $canJoinNow = $liveSession->isWithinTimeWindow() 
            && $liveSession->status !== 'cancelled' 
            && $liveSession->zoomMeeting;

        return view('student.live-sessions.show', compact('liveSession', 'attendanceLog', 'canJoinNow'));
    }
}
