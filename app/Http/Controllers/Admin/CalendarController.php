<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\CalendarEvent;
use App\Models\Subject;
use App\Models\SchoolClass;
use App\Services\CalendarService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Carbon\Carbon;

class CalendarController extends Controller
{
    public function __construct(
        private CalendarService $calendarService
    ) {}

    /**
     * عرض التقويم
     */
    public function index()
    {
        $subjects = Subject::active()->ordered()->get();
        $classes = SchoolClass::active()->ordered()->get();
        
        // جلب الأحداث لمدة 12 شهر سابق و 12 شهر قادم
        $start = Carbon::now()->subMonths(12)->startOfMonth();
        $end = Carbon::now()->addMonths(12)->endOfMonth();
        $user = Auth::user();
        
        $events = $this->calendarService->getEventsForUser($user, $start, $end);
        $formattedEvents = $this->calendarService->formatEventsForCalendar($events);
        
        return view('admin.pages.calendar.index', compact('subjects', 'classes', 'formattedEvents'));
    }

    /**
     * API endpoint لجلب الأحداث
     */
    public function getEvents(Request $request)
    {
        try {
            // FullCalendar يرسل start و end كـ ISO strings
            $start = $request->has('start') ? Carbon::parse($request->input('start')) : Carbon::now()->startOfMonth();
            $end = $request->has('end') ? Carbon::parse($request->input('end')) : Carbon::now()->endOfMonth();
            $user = Auth::user();

            $events = $this->calendarService->getEventsForUser($user, $start, $end);
            $formattedEvents = $this->calendarService->formatEventsForCalendar($events);

            Log::info('Calendar events API called', [
                'user_id' => $user->id,
                'start' => $start->toDateString(),
                'end' => $end->toDateString(),
                'events_count' => count($formattedEvents),
            ]);

            return response()->json($formattedEvents);
        } catch (\Exception $e) {
            Log::error('Error in calendar events API: ' . $e->getMessage(), [
                'trace' => $e->getTraceAsString()
            ]);
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

    /**
     * عرض نموذج إنشاء حدث
     */
    public function create()
    {
        $subjects = Subject::active()->ordered()->get();
        $classes = SchoolClass::active()->ordered()->get();
        $eventTypes = CalendarEvent::EVENT_TYPES;

        return view('admin.pages.calendar.events.create', compact('subjects', 'classes', 'eventTypes'));
    }

    /**
     * حفظ حدث جديد
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'event_type' => 'required|in:general,meeting,holiday,exam,other',
            'start_date' => 'required|date',
            'end_date' => 'nullable|date|after_or_equal:start_date',
            'is_all_day' => 'boolean',
            'location' => 'nullable|string|max:255',
            'color' => 'nullable|string|max:7',
            'subject_id' => 'nullable|exists:subjects,id',
            'class_id' => 'nullable|exists:classes,id',
            'is_public' => 'boolean',
        ], [
            'title.required' => 'العنوان مطلوب',
            'event_type.required' => 'نوع الحدث مطلوب',
            'start_date.required' => 'تاريخ البدء مطلوب',
            'end_date.after_or_equal' => 'تاريخ الانتهاء يجب أن يكون بعد أو يساوي تاريخ البدء',
        ]);

        try {
            $validated['created_by'] = Auth::id();
            $validated['is_all_day'] = $request->has('is_all_day');
            $validated['is_public'] = $request->has('is_public') ? true : false;
            
            // تحويل التواريخ إلى Carbon
            $validated['start_date'] = Carbon::parse($validated['start_date']);
            if (isset($validated['end_date'])) {
                $validated['end_date'] = Carbon::parse($validated['end_date']);
            }

            $event = CalendarEvent::create($validated);

            return redirect()->route('admin.calendar.index')
                           ->with('success', 'تم إنشاء الحدث بنجاح.');
        } catch (\Exception $e) {
            Log::error('Error creating calendar event: ' . $e->getMessage(), ['request' => $validated]);
            return redirect()->back()
                           ->with('error', 'حدث خطأ أثناء إنشاء الحدث: ' . $e->getMessage())
                           ->withInput();
        }
    }

    /**
     * عرض نموذج تعديل
     */
    public function edit(CalendarEvent $event)
    {
        $user = Auth::user();
        
        // صلاحيات المعلم
        if ($user->hasRole('teacher') && !$user->hasRole('admin')) {
            if ($event->created_by !== $user->id) {
                abort(403, 'يمكنك تعديل الأحداث التي أنشأتها فقط.');
            }
        }

        $subjects = Subject::active()->ordered()->get();
        $classes = SchoolClass::active()->ordered()->get();
        $eventTypes = CalendarEvent::EVENT_TYPES;

        return view('admin.pages.calendar.events.edit', compact('event', 'subjects', 'classes', 'eventTypes'));
    }

    /**
     * تحديث حدث
     */
    public function update(Request $request, CalendarEvent $event)
    {
        $user = Auth::user();
        
        // صلاحيات المعلم
        if ($user->hasRole('teacher') && !$user->hasRole('admin')) {
            if ($event->created_by !== $user->id) {
                abort(403, 'يمكنك تحديث الأحداث التي أنشأتها فقط.');
            }
        }

        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'event_type' => 'required|in:general,meeting,holiday,exam,other',
            'start_date' => 'required|date',
            'end_date' => 'nullable|date|after_or_equal:start_date',
            'is_all_day' => 'boolean',
            'location' => 'nullable|string|max:255',
            'color' => 'nullable|string|max:7',
            'subject_id' => 'nullable|exists:subjects,id',
            'class_id' => 'nullable|exists:classes,id',
            'is_public' => 'boolean',
        ], [
            'title.required' => 'العنوان مطلوب',
            'event_type.required' => 'نوع الحدث مطلوب',
            'start_date.required' => 'تاريخ البدء مطلوب',
            'end_date.after_or_equal' => 'تاريخ الانتهاء يجب أن يكون بعد أو يساوي تاريخ البدء',
        ]);

        try {
            $validated['is_all_day'] = $request->has('is_all_day');
            $validated['is_public'] = $request->has('is_public') ?? true;

            $event->update($validated);

            return redirect()->route('admin.calendar.index')
                           ->with('success', 'تم تحديث الحدث بنجاح.');
        } catch (\Exception $e) {
            Log::error('Error updating calendar event: ' . $e->getMessage(), ['event_id' => $event->id, 'request' => $validated]);
            return redirect()->back()
                           ->with('error', 'حدث خطأ أثناء تحديث الحدث: ' . $e->getMessage())
                           ->withInput();
        }
    }

    /**
     * حذف حدث
     */
    public function destroy(CalendarEvent $event)
    {
        $user = Auth::user();
        
        // صلاحيات المعلم
        if ($user->hasRole('teacher') && !$user->hasRole('admin')) {
            if ($event->created_by !== $user->id) {
                abort(403, 'يمكنك حذف الأحداث التي أنشأتها فقط.');
            }
        }

        try {
            $event->delete();

            return redirect()->route('admin.calendar.index')
                           ->with('success', 'تم حذف الحدث بنجاح.');
        } catch (\Exception $e) {
            Log::error('Error deleting calendar event: ' . $e->getMessage(), ['event_id' => $event->id]);
            return redirect()->back()
                           ->with('error', 'حدث خطأ أثناء حذف الحدث.');
        }
    }
}
