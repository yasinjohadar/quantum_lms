<?php

namespace App\Services\AI;

use App\Models\AIConversation;
use App\Models\Question;
use App\Models\Lesson;

class AIPromptService
{
    /**
     * الحصول على prompt للمساعد التعليمي
     */
    public function getChatbotPrompt(AIConversation $conversation): string
    {
        $context = $conversation->getContext();
        
        $prompt = "أنت مساعد تعليمي ذكي. مهمتك مساعدة الطلاب في فهم المواد الدراسية والإجابة على أسئلتهم.\n\n";
        
        if ($context) {
            $prompt .= "السياق:\n{$context}\n\n";
        }
        
        $prompt .= "تعليمات:\n";
        $prompt .= "- قدم إجابات واضحة ومفصلة\n";
        $prompt .= "- استخدم أمثلة لتوضيح المفاهيم\n";
        $prompt .= "- شجع الطالب على التفكير\n";
        $prompt .= "- إذا لم تكن متأكداً من الإجابة، اعترف بذلك\n";
        $prompt .= "- استخدم اللغة العربية بشكل صحيح\n";
        
        return $prompt;
    }

    /**
     * الحصول على prompt لتوليد الأسئلة
     */
    public function getQuestionGenerationPrompt(string $content, array $options): string
    {
        $questionType = $options['question_type'] ?? 'mixed';
        $numberOfQuestions = $options['number_of_questions'] ?? 5;
        $difficulty = $options['difficulty_level'] ?? 'mixed';
        
        $questionTypeText = $this->getQuestionTypeText($questionType);
        $difficultyText = $this->getDifficultyText($difficulty);
        
        $prompt = "أنت معلم محترف. مهمتك إنشاء أسئلة تعليمية من المحتوى التالي:\n\n";
        $prompt .= "المحتوى:\n{$content}\n\n";
        $prompt .= "المطلوب:\n";
        $prompt .= "- عدد الأسئلة: {$numberOfQuestions}\n";
        $prompt .= "- نوع الأسئلة: {$questionTypeText}\n";
        $prompt .= "- مستوى الصعوبة: {$difficultyText}\n\n";
        $prompt .= "قم بإنشاء الأسئلة بصيغة JSON مع البنية التالية:\n";
        $prompt .= "[\n";
        $prompt .= "  {\n";
        $prompt .= "    \"type\": \"نوع السؤال\",\n";
        $prompt .= "    \"question\": \"نص السؤال\",\n";
        $prompt .= "    \"options\": [\"خيار1\", \"خيار2\", ...],\n";
        $prompt .= "    \"correct_answer\": \"الإجابة الصحيحة\",\n";
        $prompt .= "    \"explanation\": \"شرح الإجابة\",\n";
        $prompt .= "    \"difficulty\": \"easy|medium|hard\"\n";
        $prompt .= "  }\n";
        $prompt .= "]\n";
        
        return $prompt;
    }

    /**
     * الحصول على prompt لحل السؤال
     */
    public function getQuestionSolvingPrompt(Question $question): string
    {
        $prompt = "أنت معلم محترف. مهمتك حل السؤال التالي:\n\n";
        $prompt .= "نوع السؤال: {$question->type}\n";
        $prompt .= "السؤال: " . ($question->content ?? $question->title ?? '') . "\n\n";
        
        if ($question->options && $question->options->count() > 0) {
            $prompt .= "الخيارات:\n";
            foreach ($question->options as $index => $option) {
                $prompt .= ($index + 1) . ". {$option->content}\n";
            }
            $prompt .= "\n";
        }
        
        $prompt .= "المطلوب:\n";
        $prompt .= "1. حل السؤال بشكل صحيح\n";
        $prompt .= "2. شرح طريقة الحل\n";
        $prompt .= "3. تقدير درجة الثقة في الحل (0-1)\n\n";
        $prompt .= "قم بالإجابة بصيغة JSON:\n";
        $prompt .= "{\n";
        $prompt .= "  \"solution\": \"الحل\",\n";
        $prompt .= "  \"explanation\": \"الشرح\",\n";
        $prompt .= "  \"confidence_score\": 0.95\n";
        $prompt .= "}\n";
        
        return $prompt;
    }

    /**
     * بناء سياق من بيانات
     */
    public function buildContext(array $data): string
    {
        $context = [];
        
        if (isset($data['subject'])) {
            $context[] = "المادة: {$data['subject']}";
        }
        
        if (isset($data['lesson'])) {
            $context[] = "الدرس: {$data['lesson']}";
        }
        
        if (isset($data['topic'])) {
            $context[] = "الموضوع: {$data['topic']}";
        }
        
        return implode("\n", $context);
    }

    /**
     * الحصول على نص نوع السؤال
     */
    private function getQuestionTypeText(string $type): string
    {
        $types = [
            'single_choice' => 'اختيار واحد',
            'multiple_choice' => 'اختيار متعدد',
            'true_false' => 'صح/خطأ',
            'short_answer' => 'إجابة قصيرة',
            'essay' => 'مقالي',
            'matching' => 'مطابقة',
            'ordering' => 'ترتيب',
            'fill_blanks' => 'ملء الفراغات',
            'numerical' => 'رقمي',
            'drag_drop' => 'سحب وإفلات',
            'mixed' => 'مختلط (جميع الأنواع)',
        ];
        
        return $types[$type] ?? $type;
    }

    /**
     * الحصول على نص مستوى الصعوبة
     */
    private function getDifficultyText(string $difficulty): string
    {
        $difficulties = [
            'easy' => 'سهل',
            'medium' => 'متوسط',
            'hard' => 'صعب',
            'mixed' => 'مختلط',
        ];
        
        return $difficulties[$difficulty] ?? $difficulty;
    }
}

