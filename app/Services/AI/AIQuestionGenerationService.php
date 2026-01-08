<?php

namespace App\Services\AI;

use App\Models\AIQuestionGeneration;
use App\Models\Question;
use App\Models\QuestionOption;
use App\Models\Lesson;
use App\Models\User;
use App\Models\Subject;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;

class AIQuestionGenerationService
{
    public function __construct(
        private AIModelService $modelService,
        private AIPromptService $promptService
    ) {}

    /**
     * توليد أسئلة من درس
     */
    public function generateFromLesson(Lesson $lesson, array $options = []): AIQuestionGeneration
    {
        $content = $lesson->description ?? $lesson->title;
        
        // جمع محتوى إضافي من الدرس
        if ($lesson->attachments) {
            // يمكن إضافة محتوى من المرفقات
        }

        return $this->generateFromText($content, array_merge($options, [
            'lesson_id' => $lesson->id,
            'subject_id' => $lesson->unit?->section?->subject_id,
            'source_type' => 'lesson_content',
        ]));
    }

    /**
     * توليد أسئلة من نص
     */
    public function generateFromText(string $text, array $options = []): AIQuestionGeneration
    {
        $user = $options['user'] ?? auth()->user();
        $model = $options['model'] ?? $this->modelService->getBestModelFor('question_generation');

        if (!$model) {
            throw new \Exception('لا يوجد موديل AI متاح لتوليد الأسئلة');
        }

        // دعم question_types (array) أو question_type (string) للتوافق
        $questionType = $options['question_type'] ?? null;
        $questionTypes = $options['question_types'] ?? null;
        
        // إذا تم تمرير question_types، استخدمه، وإلا استخدم question_type
        if ($questionTypes && is_array($questionTypes) && count($questionTypes) > 0) {
            // استخدام question_types الجديد
            $generation = AIQuestionGeneration::create([
                'user_id' => $user->id,
                'subject_id' => $options['subject_id'] ?? null,
                'lesson_id' => $options['lesson_id'] ?? null,
                'source_type' => $options['source_type'] ?? 'manual_text',
                'source_content' => $text,
                'question_type' => 'mixed', // للتوافق مع البيانات القديمة
                'question_types' => $questionTypes,
                'number_of_questions' => $options['number_of_questions'] ?? 5,
                'difficulty_level' => $options['difficulty_level'] ?? 'mixed',
                'ai_model_id' => $model->id,
                'status' => 'pending',
            ]);
        } else {
            // استخدام question_type القديم
            $generation = AIQuestionGeneration::create([
                'user_id' => $user->id,
                'subject_id' => $options['subject_id'] ?? null,
                'lesson_id' => $options['lesson_id'] ?? null,
                'source_type' => $options['source_type'] ?? 'manual_text',
                'source_content' => $text,
                'question_type' => $questionType ?? 'mixed',
                'question_types' => null,
                'number_of_questions' => $options['number_of_questions'] ?? 5,
                'difficulty_level' => $options['difficulty_level'] ?? 'mixed',
                'ai_model_id' => $model->id,
                'status' => 'pending',
            ]);
        }

        // معالجة التوليد (يمكن أن تكون async)
        $this->processGeneration($generation);

        return $generation;
    }

    /**
     * توليد أسئلة من موضوع
     */
    public function generateFromTopic(string $topic, array $options = []): AIQuestionGeneration
    {
        return $this->generateFromText($topic, array_merge($options, [
            'source_type' => 'topic',
        ]));
    }

    /**
     * معالجة التوليد
     */
    public function processGeneration(AIQuestionGeneration $generation): array
    {
        // زيادة وقت التنفيذ إلى 3 دقائق للطلبات الطويلة
        set_time_limit(180);
        
        $generation->update(['status' => 'processing']);

        try {
            $model = $generation->model;
            if (!$model) {
                throw new \Exception('الموديل غير موجود');
            }

            // تحديد أنواع الأسئلة (question_types أولوية على question_type)
            $selectedTypes = $generation->getSelectedQuestionTypes();
            $questionTypeForPrompt = !empty($selectedTypes) && count($selectedTypes) > 0 
                ? (count($selectedTypes) === 1 ? $selectedTypes[0] : 'mixed')
                : $generation->question_type;
            
            // بناء الـ prompt
            $prompt = $this->promptService->getQuestionGenerationPrompt(
                $generation->source_content,
                [
                    'question_type' => $questionTypeForPrompt,
                    'question_types' => !empty($selectedTypes) ? $selectedTypes : null,
                    'number_of_questions' => $generation->number_of_questions,
                    'difficulty_level' => $generation->difficulty_level,
                ]
            );

            // حساب max_tokens بناءً على عدد الأسئلة (تقريباً 800 token لكل سؤال للأسئلة الطويلة)
            // زيادة العدد لضمان عدم قطع الاستجابة
            $requiredTokens = max(4000, $generation->number_of_questions * 800);
            $maxTokens = min($requiredTokens, $model->max_tokens ?: 16000);
            
            Log::info('Question generation tokens calculation', [
                'generation_id' => $generation->id,
                'required_questions' => $generation->number_of_questions,
                'calculated_tokens' => $requiredTokens,
                'max_tokens' => $maxTokens,
                'model_max_tokens' => $model->max_tokens,
            ]);
            
            // إرسال الطلب
            $provider = AIProviderFactory::create($model);
            $response = $provider->generateText($prompt, [
                'max_tokens' => $maxTokens,
                'temperature' => 0.7, // درجة حرارة معتدلة للتنوع مع الدقة
            ]);

            if (!$response || empty($response)) {
                // محاولة الحصول على معلومات أكثر من آخر خطأ
                $lastError = $provider->getLastError() ?? 'فشل في توليد الأسئلة - لم يتم الحصول على رد من API';
                
                Log::error('AI Question Generation Failed - Empty Response', [
                    'generation_id' => $generation->id,
                    'model_id' => $model->id,
                    'model_key' => $model->model_key,
                    'provider_class' => get_class($provider),
                    'last_error' => $lastError,
                    'response_type' => gettype($response),
                    'response_empty' => empty($response),
                    'response_value' => $response, // Log the actual value for debugging
                    'provider_has_error' => method_exists($provider, 'getLastError'),
                ]);
                
                throw new \Exception($lastError);
            }

            // حفظ الرد الكامل في logs للتصحيح
            Log::info('Full AI response received', [
                'generation_id' => $generation->id,
                'response_length' => strlen($response),
                'response_preview' => substr($response, 0, 1000),
                'response_full' => $response, // حفظ الرد الكامل
            ]);

            // محاولة تحليل JSON
            $questions = $this->parseGeneratedQuestions($response);

            // التحقق من صحة الأسئلة
            $validatedQuestions = $this->validateGeneratedQuestions($questions);
            
            // التحقق من العدد المطلوب
            $requiredCount = $generation->number_of_questions;
            $actualCount = count($validatedQuestions);
            $warningMessage = null;
            
            if ($actualCount < $requiredCount) {
                $missingCount = $requiredCount - $actualCount;
                $warningMessage = "تم توليد {$actualCount} سؤال فقط من {$requiredCount} المطلوبة. ({$missingCount} سؤال مفقود)";
                
                Log::warning('Question generation incomplete', [
                    'generation_id' => $generation->id,
                    'required' => $requiredCount,
                    'actual' => $actualCount,
                    'missing' => $missingCount,
                    'response_length' => strlen($response),
                ]);
            }

            // حفظ النتائج مع رسالة التحذير إن وجدت
            $generation->update([
                'status' => 'completed',
                'generated_questions' => $validatedQuestions,
                'prompt' => $prompt,
                'tokens_used' => $provider->estimateTokens($prompt . $response),
                'cost' => $model->getCost($provider->estimateTokens($prompt . $response)),
                'error_message' => $warningMessage, // نستخدم error_message لحفظ التحذير
            ]);

            return $validatedQuestions;
        } catch (\Exception $e) {
            Log::error('Error processing question generation: ' . $e->getMessage(), [
                'generation_id' => $generation->id,
            ]);

            $generation->update([
                'status' => 'failed',
                'error_message' => $e->getMessage(),
            ]);

            throw $e;
        }
    }

    /**
     * حفظ الأسئلة المولدة
     */
    public function saveGeneratedQuestions(AIQuestionGeneration $generation, array $selectedIndices = null): Collection
    {
        if ($generation->status !== 'completed') {
            throw new \Exception('التوليد لم يكتمل بعد');
        }

        $questions = $generation->generated_questions ?? [];
        $savedQuestions = collect();

        // إذا تم تحديد indices، احفظ فقط المحددة
        if ($selectedIndices !== null && !empty($selectedIndices)) {
            $filteredQuestions = [];
            foreach ($questions as $index => $questionData) {
                if (in_array($index, $selectedIndices)) {
                    $filteredQuestions[] = $questionData;
                }
            }
            $questions = $filteredQuestions;
        }

        DB::beginTransaction();
        try {
            foreach ($questions as $questionData) {
                $question = Question::create([
                    'unit_id' => $generation->lesson?->unit_id,
                    'type' => $questionData['type'] ?? 'single_choice',
                    'title' => $questionData['question'] ?? '',
                    'content' => $questionData['question'] ?? '',
                    'explanation' => $questionData['explanation'] ?? '',
                    'difficulty' => $questionData['difficulty'] ?? 'medium',
                    'default_points' => $questionData['points'] ?? 10,
                    'is_active' => true,
                    'created_by' => $generation->user_id,
                ]);

                // إضافة الخيارات إذا كانت موجودة
                if (isset($questionData['options']) && is_array($questionData['options'])) {
                    $correctAnswer = $questionData['correct_answer'] ?? '';
                    foreach ($questionData['options'] as $index => $optionText) {
                        $isCorrect = false;
                        if (is_array($correctAnswer)) {
                            $isCorrect = in_array($optionText, $correctAnswer);
                        } else {
                            $isCorrect = trim($optionText) === trim($correctAnswer);
                        }

                        QuestionOption::create([
                            'question_id' => $question->id,
                            'content' => $optionText,
                            'is_correct' => $isCorrect,
                            'order' => $index + 1,
                        ]);
                    }
                }

                $savedQuestions->push($question);
            }

            DB::commit();
            return $savedQuestions;
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error saving generated questions: ' . $e->getMessage());
            throw $e;
        }
    }

    /**
     * التحقق من صحة الأسئلة المولدة
     */
    public function validateGeneratedQuestions(array $questions): array
    {
        $validated = [];

        foreach ($questions as $question) {
            if (!isset($question['question']) || empty($question['question'])) {
                continue;
            }

            $validated[] = [
                'type' => $question['type'] ?? 'single_choice',
                'question' => $question['question'],
                'options' => $question['options'] ?? [],
                'correct_answer' => $question['correct_answer'] ?? '',
                'explanation' => $question['explanation'] ?? '',
                'difficulty' => $question['difficulty'] ?? 'medium',
                'points' => $question['points'] ?? 10,
            ];
        }

        return $validated;
    }

    /**
     * تحليل JSON للأسئلة المولدة
     */
    private function parseGeneratedQuestions(string $response): array
    {
        Log::info('Parsing AI response for questions', [
            'response_length' => strlen($response),
            'response_preview' => substr($response, 0, 500),
        ]);

        // محاولة إصلاح encoding issues
        if (!mb_check_encoding($response, 'UTF-8')) {
            $response = mb_convert_encoding($response, 'UTF-8', 'auto');
            Log::info('Fixed encoding issues in response');
        }
        
        // تنظيف الرد من markdown code blocks
        $cleanedResponse = $response;
        
        // إزالة ```json و ``` من البداية والنهاية
        $cleanedResponse = preg_replace('/^```(?:json)?\s*/i', '', trim($cleanedResponse));
        $cleanedResponse = preg_replace('/\s*```$/i', '', $cleanedResponse);
        
        // إزالة أي BOM أو characters غريبة
        $cleanedResponse = preg_replace('/^\xEF\xBB\xBF/', '', $cleanedResponse);
        $cleanedResponse = trim($cleanedResponse);
        
        // محاولة 1: تحليل JSON مباشرة
        $decoded = json_decode($cleanedResponse, true);
        if (json_last_error() === JSON_ERROR_NONE && is_array($decoded)) {
            Log::info('JSON parsed successfully (direct)', ['count' => count($decoded)]);
            return $decoded;
        }

        // محاولة 2: استخراج JSON array من النص
        if (preg_match('/\[\s*\{.*?\}\s*\]/s', $cleanedResponse, $matches)) {
            $jsonString = $matches[0];
            $decoded = json_decode($jsonString, true);
            
            if (json_last_error() === JSON_ERROR_NONE && is_array($decoded)) {
                Log::info('JSON parsed successfully (regex array)', ['count' => count($decoded)]);
                return $decoded;
            }
        }

        // محاولة 3: البحث عن [ و ] يدوياً
        $jsonStart = strpos($cleanedResponse, '[');
        $jsonEnd = strrpos($cleanedResponse, ']');

        if ($jsonStart !== false && $jsonEnd !== false && $jsonEnd > $jsonStart) {
            $jsonString = substr($cleanedResponse, $jsonStart, $jsonEnd - $jsonStart + 1);
            $decoded = json_decode($jsonString, true);
            
            if (json_last_error() === JSON_ERROR_NONE && is_array($decoded)) {
                Log::info('JSON parsed successfully (manual extraction)', ['count' => count($decoded)]);
                return $decoded;
            }
        }

        // محاولة 4: البحث عن JSON object واحد
        if (preg_match('/\{[^{}]*"question"[^{}]*\}/s', $cleanedResponse, $matches)) {
            $decoded = json_decode('[' . $matches[0] . ']', true);
            
            if (json_last_error() === JSON_ERROR_NONE && is_array($decoded)) {
                Log::info('JSON parsed successfully (single object)', ['count' => count($decoded)]);
                return $decoded;
            }
        }

        // محاولة 5: تحليل نص غير JSON (fallback)
        $questions = $this->parseTextBasedQuestions($cleanedResponse);
        if (!empty($questions)) {
            Log::info('Questions parsed from text format', ['count' => count($questions)]);
            return $questions;
        }

        Log::warning('Failed to parse questions from response', [
            'json_error' => json_last_error_msg(),
            'response' => substr($cleanedResponse, 0, 1000),
        ]);

        return [];
    }

    /**
     * محاولة تحليل الأسئلة من نص غير JSON
     */
    private function parseTextBasedQuestions(string $text): array
    {
        $questions = [];
        
        // البحث عن أنماط مثل "1. سؤال" أو "السؤال 1:"
        $patterns = [
            '/(?:سؤال|السؤال|Question)\s*(\d+)[:\.\)]\s*(.+?)(?=(?:سؤال|السؤال|Question)\s*\d+|$)/is',
            '/(\d+)[:\.\)]\s*(.+?)(?=\d+[:\.\)]|$)/s',
        ];

        foreach ($patterns as $pattern) {
            if (preg_match_all($pattern, $text, $matches, PREG_SET_ORDER)) {
                foreach ($matches as $match) {
                    $questionText = trim($match[2] ?? $match[1] ?? '');
                    if (strlen($questionText) > 10) {
                        $questions[] = [
                            'type' => 'short_answer',
                            'question' => $questionText,
                            'options' => [],
                            'correct_answer' => '',
                            'explanation' => '',
                            'difficulty' => 'medium',
                        ];
                    }
                }
                
                if (!empty($questions)) {
                    break;
                }
            }
        }

        return $questions;
    }
}

