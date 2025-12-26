<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\EventReminder;
use App\Models\User;
use App\Services\ReminderService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class ReminderController extends Controller
{
    public function __construct(
        private ReminderService $reminderService
    ) {}

    /**
     * قائمة التذكيرات
     */
    public function index(Request $request)
    {
        $query = EventReminder::with(['user']);

        if ($request->filled('event_type')) {
            $query->where('event_type', $request->input('event_type'));
        }

        if ($request->filled('is_sent')) {
            $query->where('is_sent', $request->boolean('is_sent'));
        }

        $reminders = $query->latest()->paginate(20);

        return view('admin.pages.calendar.reminders.index', compact('reminders'));
    }

    /**
     * عرض نموذج إنشاء تذكير
     */
    public function create()
    {
        $eventTypes = EventReminder::EVENT_TYPES;
        $users = User::students()->get();

        return view('admin.pages.calendar.reminders.create', compact('eventTypes', 'users'));
    }

    /**
     * حفظ تذكير جديد
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'event_type' => 'required|in:calendar_event,quiz,assignment',
            'event_id' => 'required|integer',
            'user_id' => 'nullable|exists:users,id',
            'reminder_type' => 'required|in:single,multiple',
            'custom_minutes' => 'nullable|integer|min:1|required_if:reminder_type,single',
            'reminder_times' => 'nullable|array|required_if:reminder_type,multiple',
            'reminder_times.*' => 'integer|min:1',
        ], [
            'event_type.required' => 'نوع الحدث مطلوب',
            'event_id.required' => 'معرف الحدث مطلوب',
            'reminder_type.required' => 'نوع التذكير مطلوب',
            'custom_minutes.required_if' => 'عدد الدقائق مطلوب للتذكير الواحد',
            'reminder_times.required_if' => 'أوقات التذكير مطلوبة للتذكيرات المتعددة',
        ]);

        try {
            $this->reminderService->createReminder(
                $validated['event_type'],
                $validated['event_id'],
                $validated['user_id'] ? User::find($validated['user_id']) : null,
                [
                    'reminder_type' => $validated['reminder_type'],
                    'custom_minutes' => $validated['custom_minutes'] ?? null,
                    'reminder_times' => $validated['reminder_times'] ?? null,
                ]
            );

            return redirect()->route('admin.calendar.reminders.index')
                           ->with('success', 'تم إنشاء التذكير بنجاح.');
        } catch (\Exception $e) {
            Log::error('Error creating reminder: ' . $e->getMessage(), ['request' => $validated]);
            return redirect()->back()
                           ->with('error', 'حدث خطأ أثناء إنشاء التذكير: ' . $e->getMessage())
                           ->withInput();
        }
    }

    /**
     * عرض نموذج تعديل
     */
    public function edit(EventReminder $reminder)
    {
        $eventTypes = EventReminder::EVENT_TYPES;
        $users = User::students()->get();

        return view('admin.pages.calendar.reminders.edit', compact('reminder', 'eventTypes', 'users'));
    }

    /**
     * تحديث تذكير
     */
    public function update(Request $request, EventReminder $reminder)
    {
        $validated = $request->validate([
            'user_id' => 'nullable|exists:users,id',
            'reminder_type' => 'required|in:single,multiple',
            'custom_minutes' => 'nullable|integer|min:1|required_if:reminder_type,single',
            'reminder_times' => 'nullable|array|required_if:reminder_type,multiple',
            'reminder_times.*' => 'integer|min:1',
        ], [
            'reminder_type.required' => 'نوع التذكير مطلوب',
            'custom_minutes.required_if' => 'عدد الدقائق مطلوب للتذكير الواحد',
            'reminder_times.required_if' => 'أوقات التذكير مطلوبة للتذكيرات المتعددة',
        ]);

        try {
            $reminder->update([
                'user_id' => $validated['user_id'] ?? null,
                'reminder_type' => $validated['reminder_type'],
                'custom_minutes' => $validated['custom_minutes'] ?? null,
                'reminder_times' => $validated['reminder_times'] ?? null,
                'is_sent' => false, // إعادة تعيين عند التحديث
                'sent_at' => null,
            ]);

            return redirect()->route('admin.calendar.reminders.index')
                           ->with('success', 'تم تحديث التذكير بنجاح.');
        } catch (\Exception $e) {
            Log::error('Error updating reminder: ' . $e->getMessage(), ['reminder_id' => $reminder->id, 'request' => $validated]);
            return redirect()->back()
                           ->with('error', 'حدث خطأ أثناء تحديث التذكير: ' . $e->getMessage())
                           ->withInput();
        }
    }

    /**
     * حذف تذكير
     */
    public function destroy(EventReminder $reminder)
    {
        try {
            $reminder->delete();

            return redirect()->route('admin.calendar.reminders.index')
                           ->with('success', 'تم حذف التذكير بنجاح.');
        } catch (\Exception $e) {
            Log::error('Error deleting reminder: ' . $e->getMessage(), ['reminder_id' => $reminder->id]);
            return redirect()->back()
                           ->with('error', 'حدث خطأ أثناء حذف التذكير.');
        }
    }
}
