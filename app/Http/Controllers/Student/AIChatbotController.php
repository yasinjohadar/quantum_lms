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
        $models = \App\Models\AIModel::where('is_active', true)
            ->where(function($q) {
                $q->whereJsonContains('capabilities', 'chat')
                  ->orWhereJsonContains('capabilities', 'all');
            })
            ->orderBy('priority', 'desc')
            ->orderBy('name')
            ->get();

        if ($request->filled('subject_id')) {
            $lessons = Lesson::whereHas('unit.section', function($q) use ($request) {
                $q->where('subject_id', $request->subject_id);
            })->active()->get();
        }

        return view('student.pages.ai.chatbot.create', compact('subjects', 'lessons', 'models'));
    }

    /**
     * حفظ محادثة جديدة
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'subject_id' => 'nullable|exists:subjects,id',
            'lesson_id' => 'nullable|exists:lessons,id',
            'ai_model_id' => 'nullable|exists:ai_models,id',
            'temperature' => 'nullable|numeric|min:0|max:1',
            'max_tokens' => 'nullable|integer|min:100|max:10000',
            'mode' => 'nullable|in:educational,casual,deep_analysis',
        ]);

        try {
            $subject = $validated['subject_id'] ?? null ? Subject::find($validated['subject_id']) : null;
            $lesson = $validated['lesson_id'] ?? null ? Lesson::find($validated['lesson_id']) : null;
            $model = $validated['ai_model_id'] ?? null ? \App\Models\AIModel::find($validated['ai_model_id']) : null;

            // إعدادات المحادثة
            $settings = [];
            if (isset($validated['temperature'])) {
                $settings['temperature'] = $validated['temperature'];
            }
            if (isset($validated['max_tokens'])) {
                $settings['max_tokens'] = $validated['max_tokens'];
            }
            if (isset($validated['mode'])) {
                $settings['mode'] = $validated['mode'];
            }

            $conversation = $this->chatbotService->createConversation(
                Auth::user(),
                $subject,
                $lesson,
                $model,
                $settings
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

        $conversation->load(['subject', 'lesson', 'model']);
        
        // الحصول على الرسائل مع attachments
        $messages = $this->chatbotService->getConversationHistory($conversation, 50)->load('attachments');

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
            'quick_action' => 'nullable|in:simple_explanation,example,summary,review_questions,important_terms',
            'attachments' => 'nullable|array|max:5',
            'attachments.*' => 'file|max:10240', // 10MB max per file
        ]);

        try {
            $attachments = [];
            
            // معالجة المرفقات
            if ($request->hasFile('attachments')) {
                foreach ($request->file('attachments') as $file) {
                    $attachmentData = $this->processAttachment($file);
                    if ($attachmentData) {
                        $attachments[] = $attachmentData;
                    }
                }
            }

            $message = $this->chatbotService->sendMessage(
                $conversation,
                $validated['message'],
                null,
                $validated['quick_action'] ?? null,
                $attachments
            );

            $message->load('attachments');

            return response()->json([
                'success' => true,
                'message' => [
                    'id' => $message->id,
                    'role' => $message->role,
                    'content' => $message->content,
                    'quick_action' => $message->quick_action,
                    'attachments' => $message->attachments->map(function($attachment) {
                        return [
                            'id' => $attachment->id,
                            'file_name' => $attachment->file_name,
                            'file_type' => $attachment->file_type,
                            'file_size' => $attachment->file_size,
                            'url' => $attachment->url,
                        ];
                    }),
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

    /**
     * تحديث سياق المحادثة
     */
    public function updateContext(Request $request, AIConversation $conversation)
    {
        // التحقق من الصلاحيات
        if ($conversation->user_id !== Auth::id()) {
            return response()->json(['error' => 'غير مصرح'], 403);
        }

        $validated = $request->validate([
            'subject_id' => 'nullable|exists:subjects,id',
            'lesson_id' => 'nullable|exists:lessons,id',
        ]);

        try {
            $subject = $validated['subject_id'] ?? null ? Subject::find($validated['subject_id']) : null;
            $lesson = $validated['lesson_id'] ?? null ? Lesson::find($validated['lesson_id']) : null;

            $conversation->updateContext($subject, $lesson);

            return response()->json([
                'success' => true,
                'conversation' => [
                    'id' => $conversation->id,
                    'subject_id' => $conversation->subject_id,
                    'lesson_id' => $conversation->lesson_id,
                    'conversation_type' => $conversation->conversation_type,
                    'title' => $conversation->title,
                ],
            ]);
        } catch (\Exception $e) {
            Log::error('Error updating context: ' . $e->getMessage(), [
                'conversation_id' => $conversation->id,
            ]);

            return response()->json([
                'success' => false,
                'error' => 'حدث خطأ أثناء تحديث السياق: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * إعادة تسمية المحادثة
     */
    public function rename(Request $request, AIConversation $conversation)
    {
        // التحقق من الصلاحيات
        if ($conversation->user_id !== Auth::id()) {
            return response()->json(['error' => 'غير مصرح'], 403);
        }

        $validated = $request->validate([
            'title' => 'required|string|max:255',
        ]);

        try {
            $conversation->update(['title' => $validated['title']]);

            return response()->json([
                'success' => true,
                'title' => $conversation->title,
            ]);
        } catch (\Exception $e) {
            Log::error('Error renaming conversation: ' . $e->getMessage());

            return response()->json([
                'success' => false,
                'error' => 'حدث خطأ أثناء إعادة تسمية المحادثة.',
            ], 500);
        }
    }

    /**
     * Bookmark/Unbookmark رسالة
     */
    public function toggleBookmark(Request $request, AIConversation $conversation, \App\Models\AIMessage $message)
    {
        // التحقق من الصلاحيات
        if ($conversation->user_id !== Auth::id() || $message->conversation_id !== $conversation->id) {
            return response()->json(['error' => 'غير مصرح'], 403);
        }

        try {
            $message->update(['is_bookmarked' => !$message->is_bookmarked]);

            return response()->json([
                'success' => true,
                'is_bookmarked' => $message->is_bookmarked,
            ]);
        } catch (\Exception $e) {
            Log::error('Error toggling bookmark: ' . $e->getMessage());

            return response()->json([
                'success' => false,
                'error' => 'حدث خطأ أثناء تحديث الإشارة المرجعية.',
            ], 500);
        }
    }

    /**
     * معالجة مرفق
     */
    private function processAttachment(\Illuminate\Http\UploadedFile $file): ?array
    {
        try {
            $extension = strtolower($file->getClientOriginalExtension());
            $mimeType = $file->getMimeType();

            // تحديد نوع الملف
            $fileType = 'document';
            if (in_array($extension, ['jpg', 'jpeg', 'png', 'webp', 'gif'])) {
                $fileType = 'image';
            } elseif (in_array($extension, ['txt', 'md'])) {
                $fileType = 'text';
            }

            // حفظ الملف
            $path = $file->store('ai-chatbot-attachments', 'public');
            
            // استخراج المحتوى
            $content = null;
            if ($fileType === 'image') {
                // تحويل الصورة إلى base64
                $imageContent = file_get_contents(storage_path('app/public/' . $path));
                $content = base64_encode($imageContent);
            } elseif ($fileType === 'text') {
                // قراءة الملف النصي
                $content = file_get_contents(storage_path('app/public/' . $path));
            }

            return [
                'file_name' => $file->getClientOriginalName(),
                'file_path' => $path,
                'file_type' => $fileType,
                'mime_type' => $mimeType,
                'file_size' => $file->getSize(),
                'content' => $content,
            ];
        } catch (\Exception $e) {
            Log::error('Error processing attachment: ' . $e->getMessage());
            return null;
        }
    }
}
