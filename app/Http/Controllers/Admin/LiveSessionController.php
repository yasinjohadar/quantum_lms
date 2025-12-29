<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\StoreLiveSessionRequest;
use App\Http\Requests\Admin\UpdateLiveSessionRequest;
use App\Models\LiveSession;
use App\Models\Subject;
use App\Models\Lesson;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class LiveSessionController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request): View
    {
        $this->authorize('viewAny', LiveSession::class);

        $query = LiveSession::with(['sessionable', 'creator', 'zoomMeeting']);

        // فلترة حسب البحث
        if ($request->filled('search')) {
            $search = $request->input('search');
            $query->where('title', 'like', '%' . $search . '%')
                  ->orWhere('description', 'like', '%' . $search . '%');
        }

        // فلترة حسب الحالة
        if ($request->filled('status')) {
            $query->where('status', $request->input('status'));
        }

        // فلترة حسب المادة
        if ($request->filled('subject_id')) {
            $query->where('sessionable_type', Subject::class)
                  ->where('sessionable_id', $request->input('subject_id'));
        }

        // فلترة حسب التاريخ
        if ($request->filled('date_from')) {
            $query->where('scheduled_at', '>=', $request->input('date_from'));
        }

        if ($request->filled('date_to')) {
            $query->where('scheduled_at', '<=', $request->input('date_to'));
        }

        $sessions = $query->orderBy('scheduled_at', 'desc')->paginate(15);
        $subjects = Subject::with('schoolClass')->active()->get();

        return view('admin.live-sessions.index', compact('sessions', 'subjects'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create(): View
    {
        $this->authorize('create', LiveSession::class);

        $subjects = Subject::with('schoolClass')->active()->get();
        $lessons = Lesson::with(['unit.section.subject', 'unit.section'])->active()->get();

        return view('admin.live-sessions.create', compact('subjects', 'lessons'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreLiveSessionRequest $request): RedirectResponse
    {
        $this->authorize('create', LiveSession::class);

        try {
            LiveSession::create([
                'sessionable_type' => $request->sessionable_type,
                'sessionable_id' => $request->sessionable_id,
                'title' => $request->title,
                'description' => $request->description,
                'scheduled_at' => $request->scheduled_at,
                'duration_minutes' => $request->duration_minutes,
                'timezone' => $request->timezone,
                'status' => 'scheduled',
                'created_by' => $request->user()->id,
            ]);

            return redirect()->route('admin.live-sessions.index')
                ->with('success', 'تم إنشاء الجلسة الحية بنجاح.');
        } catch (\Exception $e) {
            return redirect()->back()
                ->withInput()
                ->with('error', 'حدث خطأ أثناء إنشاء الجلسة: ' . $e->getMessage());
        }
    }

    /**
     * Display the specified resource.
     */
    public function show(LiveSession $liveSession): View
    {
        $this->authorize('view', $liveSession);

        $liveSession->load(['sessionable', 'creator', 'zoomMeeting', 'attendanceLogs.user']);

        return view('admin.live-sessions.show', compact('liveSession'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(LiveSession $liveSession): View
    {
        $this->authorize('update', $liveSession);

        return view('admin.live-sessions.edit', compact('liveSession'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateLiveSessionRequest $request, LiveSession $liveSession): RedirectResponse
    {
        $this->authorize('update', $liveSession);

        try {
            $liveSession->update($request->validated());

            return redirect()->route('admin.live-sessions.show', $liveSession)
                ->with('success', 'تم تحديث الجلسة الحية بنجاح.');
        } catch (\Exception $e) {
            return redirect()->back()
                ->withInput()
                ->with('error', 'حدث خطأ أثناء تحديث الجلسة: ' . $e->getMessage());
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(LiveSession $liveSession): RedirectResponse
    {
        $this->authorize('delete', $liveSession);

        try {
            // Cancel Zoom meeting if exists
            if ($liveSession->zoomMeeting) {
                $zoomService = app(\App\Services\Zoom\ZoomService::class);
                try {
                    $zoomService->cancelMeeting($liveSession->zoomMeeting);
                } catch (\Exception $e) {
                    // Log error but continue with deletion
                    \Log::error('Failed to cancel Zoom meeting: ' . $e->getMessage());
                }
            }

            $liveSession->delete();

            return redirect()->route('admin.live-sessions.index')
                ->with('success', 'تم حذف الجلسة الحية بنجاح.');
        } catch (\Exception $e) {
            return redirect()->back()
                ->with('error', 'حدث خطأ أثناء حذف الجلسة: ' . $e->getMessage());
        }
    }
}
