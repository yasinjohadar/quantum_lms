<?php

namespace App\Http\Controllers\Student;

use App\Http\Controllers\Controller;
use App\Models\AIConversation;
use App\Models\Subject;
use App\Models\Lesson;
use App\Services\AI\AIChatbotService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

class AIChatbotController extends Controller
{
    public function __construct(
        private AIChatbotService $chatbotService
    ) {}

    /**
     * قائمة المحادثات
     */
    public function index()
    {
        $conversations = AIConversation::forUser(Auth::id())
                                      ->with(['subject', 'lesson', 'model'])
                                      ->latest('last_message_at')
                                      ->paginate(20);

        return view('student.pages.ai.chatbot.index', compact('conversations'));
    }

    /**
     * إنشاء محادثة جديدة
     */
    public function create(Request $request)
    {
        $subjects = Auth::user()->subjects()->active()->get();
        $lessons = collect();

        if ($request->filled('subject_id')) {
            $lessons = Lesson::whereHas('unit.section', function($q) use ($request) {
                $q->where('subject_id', $request->subject_id);
            })->active()->get();
        }

        return view('student.pages.ai.chatbot.create', compact('subjects', 'lessons'));
    }

    /**
     * حفظ محادثة جديدة
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'subject_id' => 'nullable|exists:subjects,id',
            'lesson_id' => 'nullable|exists:lessons,id',
        ]);

        try {
            $subject = $validated['subject_id'] ? Subject::find($validated['subject_id']) : null;
            $lesson = $validated['lesson_id'] ? Lesson::find($validated['lesson_id']) : null;

            $conversation = $this->chatbotService->createConversation(
                Auth::user(),
                $subject,
                $lesson
            );

            return redirect()->route('student.ai.chatbot.show', $conversation)
                           ->with('success', 'تم إنشاء المحادثة بنجاح.');
        } catch (\Exception $e) {
            Log::error('Error creating conversation: ' . $e->getMessage());
            return redirect()->back()
                           ->with('error', 'حدث خطأ أثناء إنشاء المحادثة: ' . $e->getMessage())
                           ->withInput();
        }
    }

    /**
     * عرض محادثة
     */
    public function show(AIConversation $conversation)
    {
        // التحقق من الصلاحيات
        if ($conversation->user_id !== Auth::id()) {
            abort(403, 'ليس لديك صلاحية لعرض هذه المحادثة.');
        }

        $conversation->load(['subject', 'lesson', 'model', 'messages']);
        $messages = $this->chatbotService->getConversationHistory($conversation, 50);

        return view('student.pages.ai.chatbot.show', compact('conversation', 'messages'));
    }

    /**
     * إرسال رسالة (AJAX)
     */
    public function sendMessage(Request $request, AIConversation $conversation)
    {
        // التحقق من الصلاحيات
        if ($conversation->user_id !== Auth::id()) {
            return response()->json(['error' => 'غير مصرح'], 403);
        }

        $validated = $request->validate([
            'message' => 'required|string|max:5000',
        ]);

        try {
            $message = $this->chatbotService->sendMessage($conversation, $validated['message']);

            return response()->json([
                'success' => true,
                'message' => [
                    'id' => $message->id,
                    'role' => $message->role,
                    'content' => $message->content,
                    'created_at' => $message->created_at->toIso8601String(),
                ],
            ]);
        } catch (\Exception $e) {
            Log::error('Error sending message: ' . $e->getMessage(), [
                'conversation_id' => $conversation->id,
            ]);

            return response()->json([
                'success' => false,
                'error' => 'حدث خطأ أثناء إرسال الرسالة: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * جلب تاريخ المحادثة (AJAX)
     */
    public function getHistory(AIConversation $conversation)
    {
        // التحقق من الصلاحيات
        if ($conversation->user_id !== Auth::id()) {
            return response()->json(['error' => 'غير مصرح'], 403);
        }

        $messages = $this->chatbotService->getConversationHistory($conversation, 50);

        return response()->json([
            'success' => true,
            'messages' => $messages->map(function($msg) {
                return [
                    'id' => $msg->id,
                    'role' => $msg->role,
                    'content' => $msg->content,
                    'created_at' => $msg->created_at->toIso8601String(),
                ];
            }),
        ]);
    }

    /**
     * حذف محادثة
     */
    public function destroy(AIConversation $conversation)
    {
        // التحقق من الصلاحيات
        if ($conversation->user_id !== Auth::id()) {
            abort(403, 'ليس لديك صلاحية لحذف هذه المحادثة.');
        }

        try {
            $conversation->delete();

            return redirect()->route('student.ai.chatbot.index')
                           ->with('success', 'تم حذف المحادثة بنجاح.');
        } catch (\Exception $e) {
            Log::error('Error deleting conversation: ' . $e->getMessage());
            return redirect()->back()
                           ->with('error', 'حدث خطأ أثناء حذف المحادثة.');
        }
    }
}
