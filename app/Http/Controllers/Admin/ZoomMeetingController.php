<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\CreateZoomMeetingRequest;
use App\Http\Requests\Admin\UpdateZoomMeetingRequest;
use App\Models\LiveSession;
use App\Services\Zoom\ZoomService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\View\View;

class ZoomMeetingController extends Controller
{
    protected ZoomService $zoomService;

    public function __construct(ZoomService $zoomService)
    {
        $this->zoomService = $zoomService;
    }

    /**
     * Create Zoom meeting for a live session
     */
    public function create(CreateZoomMeetingRequest $request, LiveSession $liveSession): RedirectResponse
    {
        $this->authorize('manageZoom', $liveSession);

        try {
            // Create or update live session
            $liveSession->update([
                'title' => $request->title,
                'description' => $request->description,
                'scheduled_at' => $request->scheduled_at,
                'duration_minutes' => $request->duration_minutes,
                'timezone' => $request->timezone,
            ]);

            // Create Zoom meeting
            $zoomMeeting = $this->zoomService->createMeetingForSession($liveSession, $request->user());

            return redirect()->back()
                ->with('success', 'Zoom meeting created successfully.');
        } catch (\Exception $e) {
            Log::error('Failed to create Zoom meeting: ' . $e->getMessage());
            return redirect()->back()
                ->with('error', 'Failed to create Zoom meeting: ' . $e->getMessage());
        }
    }

    /**
     * Update Zoom meeting
     */
    public function update(UpdateZoomMeetingRequest $request, LiveSession $liveSession): RedirectResponse
    {
        $this->authorize('manageZoom', $liveSession);

        $zoomMeeting = $liveSession->zoomMeeting;

        if (!$zoomMeeting) {
            return redirect()->back()
                ->with('error', 'Zoom meeting not found.');
        }

        try {
            $updateData = [];

            if ($request->has('scheduled_at')) {
                $updateData['start_time'] = $request->scheduled_at;
                $liveSession->update(['scheduled_at' => $request->scheduled_at]);
            }

            if ($request->has('duration_minutes')) {
                $updateData['duration'] = $request->duration_minutes;
                $liveSession->update(['duration_minutes' => $request->duration_minutes]);
            }

            if ($request->has('timezone')) {
                $updateData['timezone'] = $request->timezone;
                $liveSession->update(['timezone' => $request->timezone]);
            }

            if (!empty($updateData)) {
                $this->zoomService->updateMeeting($zoomMeeting, $updateData);
            }

            return redirect()->back()
                ->with('success', 'Zoom meeting updated successfully.');
        } catch (\Exception $e) {
            Log::error('Failed to update Zoom meeting: ' . $e->getMessage());
            return redirect()->back()
                ->with('error', 'Failed to update Zoom meeting: ' . $e->getMessage());
        }
    }

    /**
     * Cancel Zoom meeting
     */
    public function cancel(Request $request, LiveSession $liveSession): RedirectResponse
    {
        $this->authorize('manageZoom', $liveSession);

        $zoomMeeting = $liveSession->zoomMeeting;

        if (!$zoomMeeting) {
            return redirect()->back()
                ->with('error', 'Zoom meeting not found.');
        }

        try {
            $this->zoomService->cancelMeeting($zoomMeeting);

            $liveSession->update(['status' => 'cancelled']);

            return redirect()->back()
                ->with('success', 'Zoom meeting cancelled successfully.');
        } catch (\Exception $e) {
            Log::error('Failed to cancel Zoom meeting: ' . $e->getMessage());
            return redirect()->back()
                ->with('error', 'Failed to cancel Zoom meeting: ' . $e->getMessage());
        }
    }

    /**
     * Sync meeting status from Zoom
     */
    public function sync(LiveSession $liveSession): RedirectResponse
    {
        $this->authorize('manageZoom', $liveSession);

        $zoomMeeting = $liveSession->zoomMeeting;

        if (!$zoomMeeting) {
            return redirect()->back()
                ->with('error', 'Zoom meeting not found.');
        }

        try {
            $this->zoomService->syncMeetingStatus($zoomMeeting);

            return redirect()->back()
                ->with('success', 'Meeting status synced successfully.');
        } catch (\Exception $e) {
            Log::error('Failed to sync Zoom meeting status: ' . $e->getMessage());
            return redirect()->back()
                ->with('error', 'Failed to sync meeting status: ' . $e->getMessage());
        }
    }

    /**
     * Show manage page for Zoom meeting
     */
    public function manage(LiveSession $liveSession): View
    {
        $this->authorize('manageZoom', $liveSession);

        return view('admin.live-sessions.zoom.manage', compact('liveSession'));
    }
}
