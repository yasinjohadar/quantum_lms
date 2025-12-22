<?php

namespace App\Http\Controllers\Student;

use App\Http\Controllers\Controller;
use App\Services\CalendarService;
use App\Services\ICalExportService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;

class CalendarController extends Controller
{
    public function __construct(
        private CalendarService $calendarService,
        private ICalExportService $icalService
    ) {}

    /**
     * عرض التقويم للطالب
     */
    public function index()
    {
        return view('student.pages.calendar.index');
    }

    /**
     * API endpoint لجلب الأحداث
     */
    public function getEvents(Request $request)
    {
        $start = Carbon::parse($request->input('start'));
        $end = Carbon::parse($request->input('end'));
        $user = Auth::user();

        $events = $this->calendarService->getEventsForUser($user, $start, $end);
        $formattedEvents = $this->calendarService->formatEventsForCalendar($events);

        return response()->json($formattedEvents);
    }

    /**
     * تصدير iCal
     */
    public function export(Request $request)
    {
        $user = Auth::user();
        $start = $request->input('start') ? Carbon::parse($request->input('start')) : now()->subMonths(1);
        $end = $request->input('end') ? Carbon::parse($request->input('end')) : now()->addMonths(6);

        $events = $this->calendarService->getEventsForUser($user, $start, $end);

        return $this->icalService->generateICSFile($events, $user);
    }
}
