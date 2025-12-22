<?php

namespace App\Services\AI;

use App\Models\AIQuestionSolution;
use App\Models\Question;
use App\Models\User;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Log;

class AIQuestionSolvingService
{
    public function __construct(
        private AIModelService $modelService,
        private AIPromptService $promptService
    ) {}

    /**
     * حل سؤال واحد
     */
    public function solveQuestion(Question $question, ?AIModel $model = null): AIQuestionSolution
    {
        // التحقق من وجود حل سابق
        $existingSolution = AIQuestionSolution::where('question_id', $question->id)
                                             ->where('ai_model_id', $model?->id)
                                             ->first();

        if ($existingSolution) {
            return $existingSolution;
        }

        // الحصول على الموديل
        if (!$model) {
            $model = $this->modelService->getBestModelFor('question_solving');
        }

        if (!$model) {
            throw new \Exception('لا يوجد موديل AI متاح لحل الأسئلة');
        }

        try {
            // بناء الـ prompt
            $prompt = $this->promptService->getQuestionSolvingPrompt($question);

            // إرسال الطلب
            $provider = AIProviderFactory::create($model);
            $response = $provider->generateText($prompt, [
                'max_tokens' => $model->max_tokens,
                'temperature' => $model->temperature,
            ]);

            // تحليل الاستجابة
            $solutionData = $this->parseSolutionResponse($response);

            // إنشاء الحل
            $solution = AIQuestionSolution::create([
                'question_id' => $question->id,
                'ai_model_id' => $model->id,
                'solution' => $solutionData['solution'] ?? $response,
                'explanation' => $solutionData['explanation'] ?? '',
                'confidence_score' => $solutionData['confidence_score'] ?? 0.5,
                'tokens_used' => $provider->estimateTokens($prompt . $response),
                'cost' => $model->getCost($provider->estimateTokens($prompt . $response)),
            ]);

            return $solution;
        } catch (\Exception $e) {
            Log::error('Error solving question: ' . $e->getMessage(), [
                'question_id' => $question->id,
                'model_id' => $model->id,
            ]);

            throw $e;
        }
    }

    /**
     * حل عدة أسئلة
     */
    public function solveMultipleQuestions(Collection $questions, ?AIModel $model = null): Collection
    {
        $solutions = collect();

        foreach ($questions as $question) {
            try {
                $solution = $this->solveQuestion($question, $model);
                $solutions->push($solution);
            } catch (\Exception $e) {
                Log::error('Error solving question in batch: ' . $e->getMessage(), [
                    'question_id' => $question->id,
                ]);
            }
        }

        return $solutions;
    }

    /**
     * التحقق من حل
     */
    public function verifySolution(AIQuestionSolution $solution, User $verifier): bool
    {
        return $solution->verify($verifier);
    }

    /**
     * الحصول على حل لسؤال
     */
    public function getSolutionForQuestion(Question $question): ?AIQuestionSolution
    {
        return AIQuestionSolution::forQuestion($question->id)
                                 ->verified()
                                 ->latest()
                                 ->first();
    }

    /**
     * الحصول على دقة الحل
     */
    public function getAccuracy(AIQuestionSolution $solution): float
    {
        if (!$solution->is_verified) {
            return $solution->confidence_score ?? 0;
        }

        // مقارنة الحل مع الإجابة الصحيحة
        $question = $solution->question;
        $aiAnswer = $solution->solution;
        $correctAnswer = $question->getCorrectAnswer();

        // حساب الدقة بناءً على نوع السؤال
        return match($question->type) {
            'single_choice', 'true_false' => strtolower(trim($aiAnswer)) === strtolower(trim($correctAnswer)) ? 1.0 : 0.0,
            'multiple_choice' => $this->compareMultipleChoice($aiAnswer, $correctAnswer),
            'short_answer', 'numerical' => $this->compareShortAnswer($aiAnswer, $correctAnswer),
            default => $solution->confidence_score ?? 0.5,
        };
    }

    /**
     * تحليل استجابة الحل
     */
    private function parseSolutionResponse(string $response): array
    {
        // محاولة استخراج JSON
        $jsonStart = strpos($response, '{');
        $jsonEnd = strrpos($response, '}');

        if ($jsonStart !== false && $jsonEnd !== false) {
            $jsonString = substr($response, $jsonStart, $jsonEnd - $jsonStart + 1);
            $decoded = json_decode($jsonString, true);
            
            if (json_last_error() === JSON_ERROR_NONE && is_array($decoded)) {
                return $decoded;
            }
        }

        // إذا فشل، إرجاع النص كحل
        return [
            'solution' => $response,
            'explanation' => '',
            'confidence_score' => 0.5,
        ];
    }

    /**
     * مقارنة إجابات اختيار متعدد
     */
    private function compareMultipleChoice(string $aiAnswer, $correctAnswer): float
    {
        $aiAnswers = is_array($aiAnswer) ? $aiAnswer : explode(',', $aiAnswer);
        $correctAnswers = is_array($correctAnswer) ? $correctAnswer : explode(',', $correctAnswer);

        $aiAnswers = array_map('trim', $aiAnswers);
        $correctAnswers = array_map('trim', $correctAnswers);

        $matches = count(array_intersect($aiAnswers, $correctAnswers));
        $total = count($correctAnswers);

        return $total > 0 ? $matches / $total : 0;
    }

    /**
     * مقارنة إجابات قصيرة
     */
    private function compareShortAnswer(string $aiAnswer, string $correctAnswer): float
    {
        $aiAnswer = strtolower(trim($aiAnswer));
        $correctAnswer = strtolower(trim($correctAnswer));

        if ($aiAnswer === $correctAnswer) {
            return 1.0;
        }

        // استخدام similar_text للحصول على نسبة التشابه
        similar_text($aiAnswer, $correctAnswer, $percent);
        return $percent / 100;
    }
}

