<?php

namespace App\Services\AI;

use App\Models\AIConversation;
use App\Models\AIMessage;
use App\Models\AIModel;
use App\Models\User;
use App\Models\Subject;
use App\Models\Lesson;
use App\Models\AIMessageAttachment;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Log;

class AIChatbotService
{
    public function __construct(
        private AIModelService $modelService,
        private AIPromptService $promptService
    ) {}

    /**
     * إنشاء محادثة جديدة
     */
    public function createConversation(
        User $user,
        ?Subject $subject = null,
        ?Lesson $lesson = null,
        ?AIModel $model = null,
        array $settings = []
    ): AIConversation {
        // تحديد نوع المحادثة
        $conversationType = 'general';
        if ($lesson) {
            $conversationType = 'lesson';
        } elseif ($subject) {
            $conversationType = 'subject';
        }

        // الحصول على الموديل
        if (!$model) {
            $model = $this->modelService->getBestModelFor('chat');
        }

        // إنشاء عنوان للمحادثة
        $title = null;
        if ($lesson) {
            $title = "محادثة حول: {$lesson->title}";
        } elseif ($subject) {
            $title = "محادثة حول: {$subject->name}";
        }

        $conversation = AIConversation::create([
            'user_id' => $user->id,
            'subject_id' => $subject?->id,
            'lesson_id' => $lesson?->id,
            'conversation_type' => $conversationType,
            'title' => $title,
            'ai_model_id' => $model?->id,
            'settings' => $settings,
        ]);

        // إضافة رسالة نظام
        $systemPrompt = $this->promptService->getChatbotPrompt($conversation);
        $conversation->addMessage('system', $systemPrompt);

        return $conversation;
    }

    /**
     * إرسال رسالة
     */
    public function sendMessage(
        AIConversation $conversation,
        string $message,
        ?AIModel $model = null,
        ?string $quickAction = null,
        array $attachments = []
    ): AIMessage {
        $startTime = microtime(true);

        // الحصول على الموديل
        if (!$model) {
            $model = $conversation->model ?? $this->modelService->getBestModelFor('chat');
        }

        if (!$model) {
            throw new \Exception('لا يوجد موديل AI متاح');
        }

        // الحصول على الإعدادات
        $settings = $conversation->getSettings();
        $temperature = $settings['temperature'] ?? $model->temperature ?? 0.7;
        $maxTokens = $settings['max_tokens'] ?? $model->max_tokens ?? 2000;

        // إضافة رسالة المستخدم مع Quick Action
        $userMessage = $conversation->addMessage('user', $message);
        if ($quickAction) {
            $userMessage->update(['quick_action' => $quickAction]);
        }

        // حفظ المرفقات
        if (!empty($attachments)) {
            foreach ($attachments as $attachment) {
                $this->saveAttachment($userMessage, $attachment);
            }
        }

        // الحصول على تاريخ المحادثة مع إدارة السياق
        $history = $this->getConversationHistoryWithContext($conversation, $model, $maxTokens);
        
        $messages = $history->map(function($msg) {
            $messageData = [
                'role' => $msg->role,
                'content' => $msg->content,
            ];

            // إضافة المرفقات للرسائل التي تحتوي عليها
            if ($msg->hasAttachments()) {
                $attachments = $msg->attachments;
                foreach ($attachments as $attachment) {
                    if ($attachment->isImage() && $attachment->content) {
                        // إضافة الصور للرسائل (للموديلات المدعومة)
                        $messageData['images'] = $messageData['images'] ?? [];
                        $messageData['images'][] = $attachment->content;
                    }
                }
            }

            return $messageData;
        })->toArray();

        // إرسال الطلب إلى AI
        try {
            $provider = AIProviderFactory::create($model);
            
            // تحديث prompt إذا كان هناك Quick Action
            // نبحث عن system message ونحدثه
            foreach ($messages as $index => $msg) {
                if ($msg['role'] === 'system') {
                    $messages[$index]['content'] = $this->promptService->getChatbotPrompt($conversation, $quickAction);
                    break;
                }
            }

            $options = [
                'temperature' => $temperature,
                'max_tokens' => $maxTokens,
            ];

            $response = $provider->chat($messages, $options);

            if (!$response['success']) {
                throw new \Exception($response['error'] ?? 'خطأ في الاتصال بـ AI');
            }

            // إضافة رد AI
            $assistantMessage = $conversation->addMessage('assistant', $response['content'], [
                'tokens_used' => $response['tokens_used'] ?? 0,
                'prompt_tokens' => $response['prompt_tokens'] ?? 0,
                'completion_tokens' => $response['completion_tokens'] ?? 0,
            ]);

            // تحديث التكلفة والوقت
            $responseTime = (microtime(true) - $startTime) * 1000; // بالمللي ثانية
            $cost = $model->getCost($response['tokens_used'] ?? 0);

            $assistantMessage->update([
                'tokens_used' => $response['tokens_used'] ?? 0,
                'cost' => $cost,
                'response_time' => (int) $responseTime,
            ]);

            return $assistantMessage;
        } catch (\Exception $e) {
            Log::error('Error sending AI message: ' . $e->getMessage(), [
                'conversation_id' => $conversation->id,
                'model_id' => $model->id,
                'quick_action' => $quickAction,
            ]);

            throw $e;
        }
    }

    /**
     * الحصول على تاريخ المحادثة
     */
    public function getConversationHistory(AIConversation $conversation, int $limit = 50): Collection
    {
        return $conversation->messages()
                           ->where('role', '!=', 'system')
                           ->orderBy('created_at', 'desc')
                           ->limit($limit)
                           ->get()
                           ->reverse();
    }

    /**
     * الحصول على تاريخ المحادثة مع إدارة السياق
     */
    public function getConversationHistoryWithContext(
        AIConversation $conversation,
        AIModel $model,
        int $maxTokens = 2000
    ): Collection {
        // الحصول على جميع الرسائل بما في ذلك system message مع attachments
        $allMessages = $conversation->messages()->with('attachments')->orderBy('created_at')->get();
        
        // حساب عدد الـ tokens
        $provider = AIProviderFactory::create($model);
        $totalTokens = 0;
        $messagesToInclude = collect();

        // إضافة system message أولاً
        $systemMessage = $allMessages->where('role', 'system')->first();
        if ($systemMessage) {
            $systemTokens = $provider->estimateTokens($systemMessage->content);
            if ($totalTokens + $systemTokens <= $maxTokens * 0.8) { // ترك 20% للرد
                $messagesToInclude->push($systemMessage);
                $totalTokens += $systemTokens;
            }
        }

        // إضافة الرسائل الحديثة من الأحدث للأقدم
        $recentMessages = $allMessages->where('role', '!=', 'system')->sortByDesc('created_at');
        
        foreach ($recentMessages as $message) {
            $messageTokens = $provider->estimateTokens($message->content);
            
            // إضافة tokens للمرفقات
            if ($message->hasAttachments()) {
                foreach ($message->attachments as $attachment) {
                    if ($attachment->content) {
                        $attachmentTokens = $provider->estimateTokens($attachment->content);
                        $messageTokens += $attachmentTokens;
                    }
                }
            }

            if ($totalTokens + $messageTokens <= $maxTokens * 0.8) {
                $messagesToInclude->push($message);
                $totalTokens += $messageTokens;
            } else {
                // إذا تجاوزنا الحد، نتوقف هنا
                // يمكن إضافة تلخيص للرسائل القديمة في المستقبل
                break;
            }
        }

        // ترتيب الرسائل من الأقدم للأحدث
        return $messagesToInclude->sortBy('created_at')->values();
    }

    /**
     * تلخيص الرسائل القديمة
     */
    private function summarizeOldMessages(AIConversation $conversation, int $oldMessagesCount): ?string
    {
        if ($oldMessagesCount <= 0) {
            return null;
        }

        // يمكن استخدام AI لتلخيص الرسائل القديمة هنا
        // حالياً، نعيد ملخص بسيط
        return "هناك {$oldMessagesCount} رسائل سابقة في هذه المحادثة";
    }

    /**
     * حفظ مرفق
     */
    private function saveAttachment(AIMessage $message, array $attachmentData): AIMessageAttachment
    {
        return AIMessageAttachment::create([
            'message_id' => $message->id,
            'file_name' => $attachmentData['file_name'],
            'file_path' => $attachmentData['file_path'],
            'file_type' => $attachmentData['file_type'],
            'mime_type' => $attachmentData['mime_type'] ?? null,
            'file_size' => $attachmentData['file_size'],
            'content' => $attachmentData['content'] ?? null,
        ]);
    }

    /**
     * الحصول على السياق للمحادثة
     */
    public function getContextForConversation(AIConversation $conversation): string
    {
        return $conversation->getContext();
    }

    /**
     * تقدير التكلفة
     */
    public function estimateCost(AIConversation $conversation, string $message): float
    {
        $model = $conversation->model ?? $this->modelService->getBestModelFor('chat');
        if (!$model) {
            return 0;
        }

        $provider = AIProviderFactory::create($model);
        $estimatedTokens = $provider->estimateTokens($message);
        
        return $model->getCost($estimatedTokens);
    }
}

