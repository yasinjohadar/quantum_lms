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
        
        // تحديد أنواع الأسئلة المطلوبة
        $typeInstructions = $this->getTypeInstructions($questionType, $numberOfQuestions);
        
        $prompt = "أنت خبير تعليمي متخصص في إنشاء الأسئلة والاختبارات.\n\n";
        $prompt .= "## المهمة:\n";
        $prompt .= "أنشئ بالضبط **{$numberOfQuestions} أسئلة** تعليمية متنوعة من المحتوى التالي.\n";
        $prompt .= "⚠️ **مهم جداً**: يجب أن يكون العدد بالضبط {$numberOfQuestions} أسئلة، لا أكثر ولا أقل.\n\n";
        $prompt .= "## المحتوى:\n{$content}\n\n";
        $prompt .= "## المتطلبات:\n";
        $prompt .= "1. **العدد المطلوب**: {$numberOfQuestions} أسئلة بالضبط (إلزامي - لا تقبل أي عدد آخر)\n";
        $prompt .= "2. **نوع الأسئلة**: {$questionTypeText}\n";
        $prompt .= "3. **مستوى الصعوبة**: {$difficultyText}\n";
        $prompt .= "{$typeInstructions}\n\n";
        $prompt .= "## تنسيق الإخراج:\n";
        $prompt .= "أرجع JSON array يحتوي على **بالضبط {$numberOfQuestions} كائنات** (لا أكثر ولا أقل):\n\n";
        $prompt .= "```json\n";
        $prompt .= "[\n";
        for ($i = 1; $i <= min(3, $numberOfQuestions); $i++) {
            $prompt .= "  {\n";
            $prompt .= "    \"type\": \"single_choice|multiple_choice|true_false|short_answer\",\n";
            $prompt .= "    \"question\": \"نص السؤال {$i}\",\n";
            $prompt .= "    \"options\": [\"خيار أ\", \"خيار ب\", \"خيار ج\", \"خيار د\"],\n";
            $prompt .= "    \"correct_answer\": \"الإجابة الصحيحة\",\n";
            $prompt .= "    \"explanation\": \"شرح مختصر للإجابة\",\n";
            $prompt .= "    \"difficulty\": \"easy|medium|hard\"\n";
            $prompt .= "  }" . ($i < min(3, $numberOfQuestions) ? "," : "") . "\n";
        }
        if ($numberOfQuestions > 3) {
            $prompt .= "  ... (كرر نفس البنية للأسئلة من 4 إلى {$numberOfQuestions}) ...\n";
        }
        $prompt .= "]\n";
        $prompt .= "```\n\n";
        $prompt .= "## ⚠️ تحذير مهم:\n";
        $prompt .= "- يجب أن يحتوي الرد على **بالضبط {$numberOfQuestions} أسئلة**\n";
        $lessOne = $numberOfQuestions - 1;
        $plusOne = $numberOfQuestions + 1;
        $prompt .= "- لا تقبل أي عدد آخر (لا {$lessOne} ولا {$plusOne})\n";
        $prompt .= "- تأكد من أن JSON array يحتوي على {$numberOfQuestions} عنصر بالضبط\n";
        $prompt .= "- إذا لم تستطع إنشاء {$numberOfQuestions} أسئلة، أبلغ بذلك بوضوح\n";
        
        return $prompt;
    }
    
    /**
     * الحصول على تعليمات نوع السؤال
     */
    private function getTypeInstructions(string $type, int $count): string
    {
        if ($type === 'mixed') {
            return "4. **توزيع الأنواع**: وزّع الأسئلة بين اختيار من متعدد، صح/خطأ، وإجابة قصيرة";
        }
        
        $typeNames = [
            'single_choice' => 'اختيار واحد (4 خيارات لكل سؤال)',
            'multiple_choice' => 'اختيار متعدد (4 خيارات، يمكن أن تكون أكثر من إجابة صحيحة)',
            'true_false' => 'صح/خطأ',
            'short_answer' => 'إجابة قصيرة',
            'essay' => 'مقالي',
        ];
        
        $typeName = $typeNames[$type] ?? $type;
        return "4. **نوع كل الأسئلة**: {$typeName}";
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

