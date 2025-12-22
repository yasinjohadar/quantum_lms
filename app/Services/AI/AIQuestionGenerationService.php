<?php

namespace App\Services\AI;

use App\Models\AIQuestionGeneration;
use App\Models\Question;
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

        $generation = AIQuestionGeneration::create([
            'user_id' => $user->id,
            'subject_id' => $options['subject_id'] ?? null,
            'lesson_id' => $options['lesson_id'] ?? null,
            'source_type' => $options['source_type'] ?? 'manual_text',
            'source_content' => $text,
            'question_type' => $options['question_type'] ?? 'mixed',
            'number_of_questions' => $options['number_of_questions'] ?? 5,
            'difficulty_level' => $options['difficulty_level'] ?? 'mixed',
            'ai_model_id' => $model->id,
            'status' => 'pending',
        ]);

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
        $generation->update(['status' => 'processing']);

        try {
            $model = $generation->model;
            if (!$model) {
                throw new \Exception('الموديل غير موجود');
            }

            // بناء الـ prompt
            $prompt = $this->promptService->getQuestionGenerationPrompt(
                $generation->source_content,
                [
                    'question_type' => $generation->question_type,
                    'number_of_questions' => $generation->number_of_questions,
                    'difficulty_level' => $generation->difficulty_level,
                ]
            );

            // إرسال الطلب
            $provider = AIProviderFactory::create($model);
            $response = $provider->generateText($prompt, [
                'max_tokens' => $model->max_tokens,
                'temperature' => $model->temperature,
            ]);

            if (!$response) {
                throw new \Exception('فشل في توليد الأسئلة');
            }

            // محاولة تحليل JSON
            $questions = $this->parseGeneratedQuestions($response);

            // التحقق من صحة الأسئلة
            $validatedQuestions = $this->validateGeneratedQuestions($questions);

            // حفظ النتائج
            $generation->update([
                'status' => 'completed',
                'generated_questions' => $validatedQuestions,
                'prompt' => $prompt,
                'tokens_used' => $provider->estimateTokens($prompt . $response),
                'cost' => $model->getCost($provider->estimateTokens($prompt . $response)),
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
    public function saveGeneratedQuestions(AIQuestionGeneration $generation): Collection
    {
        if ($generation->status !== 'completed') {
            throw new \Exception('التوليد لم يكتمل بعد');
        }

        $questions = $generation->generated_questions ?? [];
        $savedQuestions = collect();

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
        // محاولة استخراج JSON من النص
        $jsonStart = strpos($response, '[');
        $jsonEnd = strrpos($response, ']');

        if ($jsonStart !== false && $jsonEnd !== false) {
            $jsonString = substr($response, $jsonStart, $jsonEnd - $jsonStart + 1);
            $decoded = json_decode($jsonString, true);
            
            if (json_last_error() === JSON_ERROR_NONE && is_array($decoded)) {
                return $decoded;
            }
        }

        // إذا فشل، محاولة تحليل النص مباشرة
        return json_decode($response, true) ?? [];
    }
}

