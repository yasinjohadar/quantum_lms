<?php

namespace App\Http\Controllers\Student;

use App\Http\Controllers\Controller;
use App\Services\CalendarService;
use App\Services\ICalExportService;
use App\Models\CalendarEvent;
use App\Models\CalendarNote;
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

    /**
     * حفظ حدث جديد
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'event_type' => 'nullable|in:general,meeting,holiday,exam,other',
            'start_date' => 'required|date',
            'end_date' => 'nullable|date|after_or_equal:start_date',
            'is_all_day' => 'boolean',
            'location' => 'nullable|string|max:255',
            'color' => 'nullable|string|max:7',
        ]);

        $user = Auth::user();

        $event = CalendarEvent::create([
            'title' => $validated['title'],
            'description' => $validated['description'] ?? null,
            'event_type' => $validated['event_type'] ?? 'general',
            'start_date' => Carbon::parse($validated['start_date']),
            'end_date' => isset($validated['end_date']) ? Carbon::parse($validated['end_date']) : null,
            'is_all_day' => $validated['is_all_day'] ?? false,
            'location' => $validated['location'] ?? null,
            'color' => $validated['color'] ?? null,
            'created_by' => $user->id,
            'is_public' => false, // أحداث شخصية فقط
        ]);

        return response()->json([
            'success' => true,
            'message' => 'تم إضافة الحدث بنجاح',
            'event' => $event,
        ]);
    }

    /**
     * عرض حدث معين
     */
    public function show(CalendarEvent $event)
    {
        $user = Auth::user();

        // التحقق من أن الحدث ملك للطالب
        if ($event->created_by !== $user->id) {
            return response()->json([
                'success' => false,
                'message' => 'غير مصرح بالوصول إلى هذا الحدث',
            ], 403);
        }

        return response()->json([
            'success' => true,
            'id' => $event->id,
            'title' => $event->title,
            'description' => $event->description,
            'event_type' => $event->event_type,
            'start_date' => $event->start_date->toISOString(),
            'end_date' => $event->end_date ? $event->end_date->toISOString() : null,
            'is_all_day' => $event->is_all_day,
            'location' => $event->location,
            'color' => $event->color,
        ]);
    }

    /**
     * تحديث حدث
     */
    public function update(Request $request, CalendarEvent $event)
    {
        $user = Auth::user();

        // التحقق من أن الحدث ملك للطالب
        if ($event->created_by !== $user->id) {
            return response()->json([
                'success' => false,
                'message' => 'غير مصرح بتعديل هذا الحدث',
            ], 403);
        }

        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'event_type' => 'nullable|in:general,meeting,holiday,exam,other',
            'start_date' => 'required|date',
            'end_date' => 'nullable|date|after_or_equal:start_date',
            'is_all_day' => 'boolean',
            'location' => 'nullable|string|max:255',
            'color' => 'nullable|string|max:7',
        ]);

        $event->update([
            'title' => $validated['title'],
            'description' => $validated['description'] ?? null,
            'event_type' => $validated['event_type'] ?? 'general',
            'start_date' => Carbon::parse($validated['start_date']),
            'end_date' => isset($validated['end_date']) ? Carbon::parse($validated['end_date']) : null,
            'is_all_day' => $validated['is_all_day'] ?? false,
            'location' => $validated['location'] ?? null,
            'color' => $validated['color'] ?? null,
        ]);

        return response()->json([
            'success' => true,
            'message' => 'تم تحديث الحدث بنجاح',
            'event' => $event,
        ]);
    }

    /**
     * حذف حدث
     */
    public function destroy(CalendarEvent $event)
    {
        $user = Auth::user();

        // التحقق من أن الحدث ملك للطالب
        if ($event->created_by !== $user->id) {
            return response()->json([
                'success' => false,
                'message' => 'غير مصرح بحذف هذا الحدث',
            ], 403);
        }

        $event->delete();

        return response()->json([
            'success' => true,
            'message' => 'تم حذف الحدث بنجاح',
        ]);
    }

    /**
     * جلب الملاحظات لنطاق زمني
     */
    public function getNotes(Request $request)
    {
        $start = Carbon::parse($request->input('start'));
        $end = Carbon::parse($request->input('end'));
        $user = Auth::user();

        $notes = CalendarNote::forUser($user->id)
            ->byDateRange($start, $end)
            ->orderBy('note_date', 'desc')
            ->orderBy('is_pinned', 'desc')
            ->get();

        return response()->json([
            'success' => true,
            'notes' => $notes,
        ]);
    }

    /**
     * حفظ ملاحظة جديدة
     */
    public function storeNote(Request $request)
    {
        $validated = $request->validate([
            'note_date' => 'required|date',
            'title' => 'nullable|string|max:255',
            'content' => 'required|string',
            'color' => 'nullable|string|max:7',
            'is_pinned' => 'boolean',
        ]);

        $user = Auth::user();

        $note = CalendarNote::create([
            'user_id' => $user->id,
            'note_date' => Carbon::parse($validated['note_date']),
            'title' => $validated['title'] ?? null,
            'content' => $validated['content'],
            'color' => $validated['color'] ?? '#fbbf24',
            'is_pinned' => $validated['is_pinned'] ?? false,
        ]);

        return response()->json([
            'success' => true,
            'message' => 'تم إضافة الملاحظة بنجاح',
            'note' => $note,
        ]);
    }

    /**
     * تحديث ملاحظة
     */
    public function updateNote(CalendarNote $note, Request $request)
    {
        $user = Auth::user();

        // التحقق من أن الملاحظة ملك للطالب
        if ($note->user_id !== $user->id) {
            return response()->json([
                'success' => false,
                'message' => 'غير مصرح بتعديل هذه الملاحظة',
            ], 403);
        }

        $validated = $request->validate([
            'note_date' => 'required|date',
            'title' => 'nullable|string|max:255',
            'content' => 'required|string',
            'color' => 'nullable|string|max:7',
            'is_pinned' => 'boolean',
        ]);

        $note->update([
            'note_date' => Carbon::parse($validated['note_date']),
            'title' => $validated['title'] ?? null,
            'content' => $validated['content'],
            'color' => $validated['color'] ?? '#fbbf24',
            'is_pinned' => $validated['is_pinned'] ?? false,
        ]);

        return response()->json([
            'success' => true,
            'message' => 'تم تحديث الملاحظة بنجاح',
            'note' => $note,
        ]);
    }

    /**
     * حذف ملاحظة
     */
    public function deleteNote(CalendarNote $note)
    {
        $user = Auth::user();

        // التحقق من أن الملاحظة ملك للطالب
        if ($note->user_id !== $user->id) {
            return response()->json([
                'success' => false,
                'message' => 'غير مصرح بحذف هذه الملاحظة',
            ], 403);
        }

        $note->delete();

        return response()->json([
            'success' => true,
            'message' => 'تم حذف الملاحظة بنجاح',
        ]);
    }

    /**
     * تثبيت/إلغاء تثبيت ملاحظة
     */
    public function pinNote(CalendarNote $note)
    {
        $user = Auth::user();

        // التحقق من أن الملاحظة ملك للطالب
        if ($note->user_id !== $user->id) {
            return response()->json([
                'success' => false,
                'message' => 'غير مصرح بتعديل هذه الملاحظة',
            ], 403);
        }

        $note->update([
            'is_pinned' => !$note->is_pinned,
        ]);

        return response()->json([
            'success' => true,
            'message' => $note->is_pinned ? 'تم تثبيت الملاحظة' : 'تم إلغاء تثبيت الملاحظة',
            'note' => $note,
        ]);
    }
}