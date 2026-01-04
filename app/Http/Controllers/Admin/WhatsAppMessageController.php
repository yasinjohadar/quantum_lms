<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\WhatsAppMessage;
use App\Models\WhatsAppBroadcast;
use App\Models\SchoolClass;
use App\Models\Subject;
use App\Models\Enrollment;
use App\Services\WhatsApp\SendWhatsAppMessage;
use App\Services\WhatsApp\BroadcastWhatsAppMessage;
use App\Jobs\BroadcastWhatsAppMessageJob;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

class WhatsAppMessageController extends Controller
{
    public function __construct(
        private SendWhatsAppMessage $sendService,
        private BroadcastWhatsAppMessage $broadcastService
    ) {}

    /**
     * Display messages list
     */
    public function index(Request $request)
    {
        $query = WhatsAppMessage::with('contact');

        // Filter by direction
        if ($request->filled('direction')) {
            $query->where('direction', $request->direction);
        }

        // Filter by status
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        // Filter by type
        if ($request->filled('type')) {
            $query->where('type', $request->type);
        }

        // Filter by date
        if ($request->filled('date_from')) {
            $query->whereDate('created_at', '>=', $request->date_from);
        }

        if ($request->filled('date_to')) {
            $query->whereDate('created_at', '<=', $request->date_to);
        }

        // Search
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('body', 'like', "%{$search}%")
                  ->orWhereHas('contact', function ($contactQuery) use ($search) {
                      $contactQuery->where('wa_id', 'like', "%{$search}%")
                                   ->orWhere('name', 'like', "%{$search}%");
                  });
            });
        }

        $messages = $query->orderBy('created_at', 'desc')->paginate(20);

        return view('admin.pages.whatsapp-messages.index', compact('messages'));
    }

    /**
     * Display message details
     */
    public function show(WhatsAppMessage $message)
    {
        $message->load('contact');
        return view('admin.pages.whatsapp-messages.show', compact('message'));
    }

    /**
     * Display send message form
     */
    public function create()
    {
        $classes = SchoolClass::active()->ordered()->get();
        $subjects = Subject::active()->with('schoolClass')->orderBy('name')->get();
        
        return view('admin.pages.whatsapp-messages.send', compact('classes', 'subjects'));
    }

    /**
     * Search students for individual messaging
     */
    public function searchStudents(Request $request)
    {
        try {
            $query = User::query();

            // Filter students only (if student role exists)
            $hasStudentRole = \Spatie\Permission\Models\Role::where('name', 'student')->exists();
            if ($hasStudentRole) {
                try {
                    $query->students();
                } catch (\Exception $e) {
                    Log::warning('Error in students scope: ' . $e->getMessage());
                }
            }

            // Filter by phone
            $query->whereNotNull('phone')
                  ->where('phone', '!=', '');

            // Search
            if ($request->filled('search')) {
                $search = $request->input('search');
                $query->where(function ($q) use ($search) {
                    $q->where('name', 'like', '%' . $search . '%')
                      ->orWhere('email', 'like', '%' . $search . '%')
                      ->orWhere('phone', 'like', '%' . $search . '%');
                      
                    if (is_numeric($search)) {
                        $q->orWhere('id', $search);
                    }
                });
            }

            $students = $query->limit(50)->get()->map(function ($student) {
                return [
                    'id' => $student->id,
                    'name' => $student->name,
                    'email' => $student->email ?? '',
                    'phone' => $student->phone ?? '',
                ];
            });

            return response()->json($students);
        } catch (\Exception $e) {
            Log::error('Error searching students: ' . $e->getMessage(), [
                'exception' => $e,
                'trace' => $e->getTraceAsString()
            ]);
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

    /**
     * Send WhatsApp message
     */
    public function send(Request $request)
    {
        $validated = $request->validate([
            'student_id' => 'nullable|exists:users,id',
            'to' => 'required_without:student_id|string|regex:/^\+[1-9]\d{1,14}$/',
            'type' => 'required|in:text,template',
            'message' => 'required_if:type,text|nullable|string|max:4096',
            'template_name' => 'required_if:type,template|nullable|string|max:255',
            'language' => 'required_if:type,template|nullable|string|max:10',
        ], [
            'student_id.exists' => 'الطالب المحدد غير موجود',
            'to.required_without' => 'رقم الهاتف مطلوب إذا لم يتم اختيار طالب',
            'to.regex' => 'رقم الهاتف يجب أن يبدأ بـ + متبوعاً برمز الدولة',
            'type.required' => 'نوع الرسالة مطلوب',
            'message.required_if' => 'نص الرسالة مطلوب',
            'template_name.required_if' => 'اسم القالب مطلوب',
            'language.required_if' => 'اللغة مطلوبة',
        ]);

        try {
            $phone = $validated['to'] ?? null;
            $student = null;
            $messageText = $validated['message'] ?? '';

            // If student_id is provided, get student and use their phone
            if (!empty($validated['student_id'])) {
                $student = User::findOrFail($validated['student_id']);
                if (!$student->phone) {
                    return redirect()->back()
                                   ->with('error', 'الطالب المحدد لا يملك رقم هاتف مسجل.')
                                   ->withInput();
                }
                $phone = $student->phone;

                // Replace placeholders if message is text type
                if ($validated['type'] === 'text' && !empty($messageText)) {
                    $messageText = $this->broadcastService->replacePlaceholders(
                        $messageText,
                        $student,
                        null,
                        null
                    );
                }
            }

            if ($validated['type'] === 'template') {
                $message = $this->sendService->sendTemplate(
                    $phone,
                    $validated['template_name'],
                    $validated['language'] ?? 'ar',
                    []
                );
            } else {
                $message = $this->sendService->sendText($phone, $messageText);
            }

            return redirect()->route('admin.whatsapp-messages.show', $message)
                           ->with('success', 'تم إرسال الرسالة بنجاح.');
        } catch (\Exception $e) {
            Log::error('Error sending WhatsApp message: ' . $e->getMessage());
            return redirect()->back()
                           ->with('error', 'فشل إرسال الرسالة: ' . $e->getMessage())
                           ->withInput();
        }
    }

    /**
     * Get students count by criteria (AJAX)
     */
    public function getStudentsCount(Request $request)
    {
        $request->validate([
            'class_id' => 'nullable|exists:classes,id',
            'subject_id' => 'nullable|exists:subjects,id',
        ]);

        $students = $this->broadcastService->getStudentsByCriteria(
            $request->class_id,
            $request->subject_id
        );

        return response()->json([
            'count' => $students->count(),
        ]);
    }

    /**
     * Send broadcast message
     */
    public function broadcast(Request $request)
    {
        $validated = $request->validate([
            'send_type' => 'required|in:individual,broadcast',
            'type' => 'required|in:text,template',
            'message' => 'required_if:type,text|nullable|string|max:4096',
            'template_name' => 'required_if:type,template|nullable|string|max:255',
            'language' => 'required_if:type,template|nullable|string|max:10',
            // Broadcast fields
            'class_id' => 'required_if:send_type,broadcast|nullable|exists:classes,id',
            'subject_id' => 'nullable|exists:subjects,id',
            // Individual field
            'to' => 'required_if:send_type,individual|nullable|string|regex:/^\+[1-9]\d{1,14}$/',
        ], [
            'send_type.required' => 'نوع الإرسال مطلوب',
            'type.required' => 'نوع الرسالة مطلوب',
            'message.required_if' => 'نص الرسالة مطلوب',
            'class_id.required_if' => 'الصف الدراسي مطلوب للإرسال الجماعي',
            'class_id.exists' => 'الصف الدراسي المحدد غير موجود',
            'subject_id.exists' => 'المادة الدراسية المحددة غير موجودة',
            'to.required_if' => 'رقم الهاتف مطلوب للإرسال الفردي',
        ]);

        try {
            if ($validated['send_type'] === 'individual') {
                // Redirect to regular send method
                return $this->send($request);
            }

            // Broadcast logic
            $students = $this->broadcastService->getStudentsByCriteria(
                $validated['class_id'] ?? null,
                $validated['subject_id'] ?? null
            );

            if ($students->isEmpty()) {
                return redirect()->back()
                    ->with('error', 'لا يوجد طلاب مطابقون للمعايير المحددة.')
                    ->withInput();
            }

            // Get subject and class for placeholders
            $subject = $validated['subject_id'] ? Subject::with('schoolClass')->find($validated['subject_id']) : null;
            $class = $validated['class_id'] ? SchoolClass::find($validated['class_id']) : ($subject && $subject->schoolClass ? $subject->schoolClass : null);

            // Create broadcast record
            $broadcast = WhatsAppBroadcast::create([
                'message_template' => $validated['message'] ?? $validated['template_name'] ?? '',
                'send_type' => $validated['type'],
                'class_id' => $validated['class_id'] ?? null,
                'subject_id' => $validated['subject_id'] ?? null,
                'total_recipients' => $students->count(),
                'status' => WhatsAppBroadcast::STATUS_PENDING,
                'created_by' => Auth::id(),
            ]);

            // Dispatch jobs for each student
            foreach ($students as $student) {
                $message = $this->broadcastService->replacePlaceholders(
                    $validated['message'] ?? '',
                    $student,
                    $subject,
                    $class
                );

                BroadcastWhatsAppMessageJob::dispatch($broadcast, $student, $message, $validated['type']);
            }

            return redirect()->route('admin.whatsapp-messages.index')
                ->with('success', 'تم بدء إرسال ' . $students->count() . ' رسالة جماعية.');
        } catch (\Exception $e) {
            Log::error('Error sending broadcast message: ' . $e->getMessage());
            return redirect()->back()
                ->with('error', 'فشل إرسال الرسالة الجماعية: ' . $e->getMessage())
                ->withInput();
        }
    }
}
