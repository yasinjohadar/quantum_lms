<?php

namespace App\Services\AI;

use App\DTOs\AI\EssayGradingResultDTO;
use App\Models\AIModel;
use App\Models\Question;
use App\Models\QuizAnswer;
use App\Models\QuestionAnswer;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;

class AIEssayGradingService
{
    public function __construct(
        private AIModelService $modelService,
        private AIPromptService $promptService
    ) {}

    /**
     * تصحيح إجابة مقالية واحدة
     */
    public function gradeEssay(QuizAnswer|QuestionAnswer $answer, array $criteria = []): EssayGradingResultDTO
    {
        // زيادة وقت التنفيذ إلى 3 دقائق للطلبات الطويلة
        set_time_limit(180);
        
        $question = $answer->question;
        
        if ($question->type !== 'essay') {
            throw new \Exception('هذا السؤال ليس مقالي');
        }

        $studentAnswer = $answer->answer_text ?? '';
        if (empty($studentAnswer)) {
            throw new \Exception('لا توجد إجابة للطالب');
        }

        // الحصول على الموديل
        $model = $this->modelService->getBestModelFor('question_solving');
        if (!$model) {
            throw new \Exception('لا يوجد موديل AI متاح للتصحيح');
        }

        // الحصول على معايير التصحيح (من الإعدادات أو المعاملات)
        if (empty($criteria)) {
            $criteria = $this->getDefaultCriteria($question);
        }

        try {
            // بناء الـ prompt
            $prompt = $this->promptService->getEssayGradingPrompt($question, $studentAnswer, $criteria);

            // إرسال الطلب
            $provider = AIProviderFactory::create($model);
            $response = $provider->generateText($prompt, [
                'max_tokens' => $model->max_tokens,
                'temperature' => 0.3, // درجة حرارة منخفضة لدقة أكبر في التصحيح
            ]);

            if (empty($response)) {
                throw new \Exception('لم يتم الحصول على رد من AI');
            }

            // تحليل الاستجابة
            $gradingData = $this->parseGradingResponse($response, $answer->max_points);

            // حفظ البيانات في answer
            $answer->ai_graded = true;
            $answer->ai_grading_data = $gradingData->toArray();
            $answer->ai_graded_at = now();
            $answer->ai_grading_model_id = $model->id;
            
            // تطبيق الدرجة
            $answer->points_earned = min($gradingData->points, $answer->max_points);
            $answer->is_correct = $gradingData->points >= $answer->max_points;
            $answer->is_partially_correct = $gradingData->points > 0 && $gradingData->points < $answer->max_points;
            $answer->feedback = $gradingData->feedback;
            $answer->is_graded = true;
            $answer->graded_by = null; // AI grading
            $answer->graded_at = now();
            $answer->save();

            // تحديث درجة المحاولة
            if ($answer instanceof QuizAnswer) {
                $answer->attempt->calculateScore();
            } elseif ($answer instanceof QuestionAnswer) {
                $answer->attempt->calculateScore();
            }

            return $gradingData;
        } catch (\Exception $e) {
            Log::error('Error grading essay with AI: ' . $e->getMessage(), [
                'answer_id' => $answer->id,
                'question_id' => $question->id,
            ]);

            throw $e;
        }
    }

    /**
     * تصحيح عدة إجابات مقالية
     */
    public function gradeMultipleEssays(Collection $answers, array $criteria = []): Collection
    {
        // زيادة وقت التنفيذ إلى 3 دقائق للطلبات الطويلة
        set_time_limit(180);
        
        $results = collect();

        foreach ($answers as $answer) {
            try {
                $result = $this->gradeEssay($answer, $criteria);
                $results->push([
                    'answer' => $answer,
                    'result' => $result,
                    'success' => true,
                ]);
            } catch (\Exception $e) {
                Log::error('Error grading essay in batch: ' . $e->getMessage(), [
                    'answer_id' => $answer->id,
                ]);
                
                $results->push([
                    'answer' => $answer,
                    'result' => null,
                    'success' => false,
                    'error' => $e->getMessage(),
                ]);
            }
        }

        return $results;
    }

    /**
     * الحصول على معايير التصحيح الافتراضية
     */
    private function getDefaultCriteria(Question $question): array
    {
        // يمكن جلب المعايير من system settings أو من السؤال نفسه
        return [
            'content_completeness' => [
                'weight' => 0.3,
                'description' => 'المحتوى والشمولية - مدى شمولية الإجابة وتغطيتها للموضوع',
            ],
            'organization' => [
                'weight' => 0.25,
                'description' => 'التنظيم والترابط - تنظيم الأفكار وترابطها',
            ],
            'language' => [
                'weight' => 0.2,
                'description' => 'اللغة والقواعد - صحة اللغة والقواعد النحوية',
            ],
            'critical_thinking' => [
                'weight' => 0.15,
                'description' => 'الإبداع والتفكير النقدي - مدى عمق التحليل والتفكير',
            ],
            'length_detail' => [
                'weight' => 0.1,
                'description' => 'الطول والتفصيل - مدى التفصيل في الإجابة',
            ],
        ];
    }

    /**
     * تحليل استجابة AI للتصحيح
     */
    private function parseGradingResponse(string $response, float $maxPoints): EssayGradingResultDTO
    {
        Log::info('Parsing AI grading response', [
            'response_length' => strlen($response),
            'response_preview' => substr($response, 0, 500),
        ]);

        // تنظيف الرد من markdown code blocks
        $cleanedResponse = preg_replace('/^```(?:json)?\s*/i', '', trim($response));
        $cleanedResponse = preg_replace('/\s*```$/i', '', $cleanedResponse);

        // محاولة تحليل JSON
        $jsonStart = strpos($cleanedResponse, '{');
        $jsonEnd = strrpos($cleanedResponse, '}');

        if ($jsonStart !== false && $jsonEnd !== false) {
            $jsonString = substr($cleanedResponse, $jsonStart, $jsonEnd - $jsonStart + 1);
            $decoded = json_decode($jsonString, true);

            if (json_last_error() === JSON_ERROR_NONE && is_array($decoded)) {
                return $this->buildGradingDTO($decoded, $maxPoints);
            }
        }

        // إذا فشل JSON، محاولة استخراج البيانات من النص
        return $this->parseTextBasedGrading($cleanedResponse, $maxPoints);
    }

    /**
     * بناء DTO من بيانات JSON
     */
    private function buildGradingDTO(array $data, float $maxPoints): EssayGradingResultDTO
    {
        $points = min((float) ($data['points'] ?? $data['score'] ?? 0), $maxPoints);
        $criteriaScores = $data['criteria_scores'] ?? $data['criteria'] ?? [];
        $feedback = $data['feedback'] ?? $data['comment'] ?? '';
        $strengths = $data['strengths'] ?? $data['positive_points'] ?? [];
        $weaknesses = $data['weaknesses'] ?? $data['negative_points'] ?? [];
        $suggestions = $data['suggestions'] ?? $data['recommendations'] ?? [];

        return new EssayGradingResultDTO(
            points: $points,
            max_points: $maxPoints,
            criteria_scores: $criteriaScores,
            feedback: $feedback,
            strengths: is_array($strengths) ? $strengths : [],
            weaknesses: is_array($weaknesses) ? $weaknesses : [],
            suggestions: is_array($suggestions) ? $suggestions : [],
        );
    }

    /**
     * تحليل تصحيح من نص غير JSON
     */
    private function parseTextBasedGrading(string $text, float $maxPoints): EssayGradingResultDTO
    {
        // محاولة استخراج الدرجة من النص
        $points = 0;
        if (preg_match('/(?:درجة|نقاط|score|points?)[\s:]*(\d+(?:\.\d+)?)/i', $text, $matches)) {
            $points = min((float) $matches[1], $maxPoints);
        } elseif (preg_match('/(\d+(?:\.\d+)?)\s*(?:من|out of|/)\s*(\d+(?:\.\d+)?)/i', $text, $matches)) {
            $earned = (float) $matches[1];
            $total = (float) $matches[2];
            if ($total > 0) {
                $points = min(($earned / $total) * $maxPoints, $maxPoints);
            }
        }

        // استخراج نقاط القوة والضعف
        $strengths = $this->extractSection($text, ['نقاط القوة', 'إيجابيات', 'strengths', 'positive']);
        $weaknesses = $this->extractSection($text, ['نقاط الضعف', 'سلبيات', 'weaknesses', 'negative']);
        $suggestions = $this->extractSection($text, ['اقتراحات', 'توصيات', 'suggestions', 'recommendations']);

        return new EssayGradingResultDTO(
            points: $points,
            max_points: $maxPoints,
            criteria_scores: [],
            feedback: $text,
            strengths: $strengths,
            weaknesses: $weaknesses,
            suggestions: $suggestions,
        );
    }

    /**
     * استخراج قسم معين من النص
     */
    private function extractSection(string $text, array $keywords): array
    {
        $items = [];
        
        foreach ($keywords as $keyword) {
            $pattern = '/(' . preg_quote($keyword, '/') . ')[\s:]*\n?([^\n]+(?:\n[^\n]+)*?)(?=\n\s*(?:نقاط|إيجابيات|سلبيات|اقتراحات|strengths|weaknesses|suggestions|$))/is';
            if (preg_match($pattern, $text, $matches)) {
                $section = trim($matches[2] ?? '');
                // تقسيم إلى عناصر (bullet points)
                $lines = preg_split('/[\n•\-\*]/', $section);
                foreach ($lines as $line) {
                    $line = trim($line);
                    if (!empty($line)) {
                        $items[] = $line;
                    }
                }
                break;
            }
        }

        return $items;
    }
}

