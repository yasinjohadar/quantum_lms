<?php

namespace App\Services\AI;

use App\Models\ContentSummary;
use App\Models\AIModel;
use App\Models\Lesson;
use App\Models\Subject;
use Illuminate\Support\Facades\Log;

class AIContentSummaryService
{
    public function __construct(
        private AIModelService $modelService,
        private AIPromptService $promptService
    ) {}

    /**
     * تلخيص محتوى عام
     */
    public function summarize(string $content, string $type = 'short', ?AIModel $model = null): ContentSummary
    {
        if (!$model) {
            $model = $this->modelService->getBestModelFor('question_solving');
        }

        if (!$model) {
            throw new \Exception('لا يوجد موديل AI متاح للتلخيص');
        }

        try {
            $prompt = $this->promptService->getContentSummaryPrompt($content, $type);
            
            $provider = AIProviderFactory::create($model);
            $response = $provider->generateText($prompt, [
                'max_tokens' => $model->max_tokens,
                'temperature' => 0.5,
            ]);

            $tokensUsed = $provider->estimateTokens($prompt . $response);
            $cost = $model->getCost($tokensUsed);

            $summary = ContentSummary::create([
                'summarizable_type' => 'manual',
                'summarizable_id' => 0,
                'summary_text' => $response,
                'summary_type' => $type,
                'ai_model_id' => $model->id,
                'tokens_used' => $tokensUsed,
                'cost' => $cost,
                'created_by' => auth()->id(),
            ]);

            return $summary;
        } catch (\Exception $e) {
            Log::error('Error summarizing content: ' . $e->getMessage());
            throw $e;
        }
    }

    /**
     * تلخيص درس
     */
    public function summarizeLesson(Lesson $lesson, string $type = 'short', ?AIModel $model = null): ContentSummary
    {
        $content = $lesson->content ?? '';
        if (empty($content)) {
            throw new \Exception('الدرس لا يحتوي على محتوى للتلخيص');
        }

        $summary = $this->summarize($content, $type, $model);
        $summary->summarizable_type = Lesson::class;
        $summary->summarizable_id = $lesson->id;
        $summary->save();

        return $summary;
    }

    /**
     * تلخيص مادة
     */
    public function summarizeSubject(Subject $subject, string $type = 'short', ?AIModel $model = null): ContentSummary
    {
        // جمع محتوى الدروس في المادة
        $lessons = $subject->lessons;
        $content = $lessons->map(function ($lesson) {
            return ($lesson->title ?? '') . "\n\n" . ($lesson->content ?? '');
        })->join("\n\n---\n\n");

        if (empty($content)) {
            throw new \Exception('المادة لا تحتوي على محتوى للتلخيص');
        }

        $summary = $this->summarize($content, $type, $model);
        $summary->summarizable_type = Subject::class;
        $summary->summarizable_id = $subject->id;
        $summary->save();

        return $summary;
    }
}

