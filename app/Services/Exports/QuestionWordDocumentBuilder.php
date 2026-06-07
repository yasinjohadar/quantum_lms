<?php

namespace App\Services\Exports;

use App\Models\Question;
use App\Support\QuestionExportContentRenderer;
use Illuminate\Support\Collection;
use PhpOffice\PhpWord\PhpWord;
use PhpOffice\PhpWord\IOFactory;
use PhpOffice\PhpWord\SimpleType\Jc;
use PhpOffice\PhpWord\Style\Language;

class QuestionWordDocumentBuilder
{
    private const FONT = 'Arial';

    /**
     * @param  Collection<int, Question>  $questions
     * @param  array{title: string, subtitle?: string, note?: string}  $meta
     */
    public function saveToTempFile(Collection $questions, array $meta, string $order): string
    {
        $phpWord = new PhpWord;
        $phpWord->getSettings()->setThemeFontLang(new Language(null, null, 'ar-SA'));
        $phpWord->setDefaultFontName(self::FONT);
        $phpWord->setDefaultFontSize(12);

        $phpWord->addTitleStyle(1, ['bold' => true, 'size' => 18, 'rtl' => true, 'name' => self::FONT], ['alignment' => Jc::END]);
        $phpWord->addTitleStyle(2, ['bold' => true, 'size' => 14, 'rtl' => true, 'name' => self::FONT], ['alignment' => Jc::END]);

        $section = $phpWord->addSection([
            'rtl' => true,
            'marginTop' => 900,
            'marginBottom' => 900,
            'marginLeft' => 900,
            'marginRight' => 900,
        ]);

        $this->addCover($section, $meta, $questions->count());

        if ($order === 'by_type') {
            $this->addQuestionsGroupedByType($section, $questions);
        } else {
            $this->addQuestionsList($section, $questions);
        }

        $temp = tempnam(sys_get_temp_dir(), 'qword_');
        if ($temp === false) {
            throw new QuestionWordExportException('تعذّر إنشاء ملف مؤقت للتصدير.');
        }

        $path = $temp.'.docx';
        @unlink($temp);

        $writer = IOFactory::createWriter($phpWord, 'Word2007');
        $writer->save($path);

        return $path;
    }

    private function addCover(\PhpOffice\PhpWord\Element\Section $section, array $meta, int $count): void
    {
        $section->addTitle($meta['title'] ?? 'بنك الأسئلة', 1);
        if (! empty($meta['subtitle'])) {
            $this->addRtlText($section, $meta['subtitle'], ['size' => 12, 'color' => '555555']);
        }
        $this->addRtlText($section, 'عدد الأسئلة: '.$count, ['bold' => true]);
        $this->addRtlText($section, 'التاريخ: '.now()->format('Y-m-d H:i'), ['size' => 11, 'color' => '666666']);
        $this->addRtlText($section, $meta['note'] ?? 'مفتاح معلم — يتضمن الإجابات الصحيحة والشرح', ['italic' => true, 'color' => '1D4ED8']);
        $section->addTextBreak(1);
    }

    /**
     * @param  Collection<int, Question>  $questions
     */
    private function addQuestionsGroupedByType(\PhpOffice\PhpWord\Element\Section $section, Collection $questions): void
    {
        $grouped = $questions->groupBy('type');
        $number = 1;

        foreach (Question::TYPES as $type => $label) {
            if (! $grouped->has($type)) {
                continue;
            }

            $section->addTitle($label, 2);
            foreach ($grouped->get($type) as $question) {
                $this->addQuestionBlock($section, $question, $number);
                $number++;
            }
            $section->addTextBreak(1);
        }
    }

    /**
     * @param  Collection<int, Question>  $questions
     */
    private function addQuestionsList(\PhpOffice\PhpWord\Element\Section $section, Collection $questions): void
    {
        $number = 1;
        foreach ($questions as $question) {
            $this->addQuestionBlock($section, $question, $number);
            $number++;
        }
    }

    private function addQuestionBlock(\PhpOffice\PhpWord\Element\Section $section, Question $question, int $number): void
    {
        $header = sprintf(
            'السؤال %d — %s — %s — %.2f نقطة',
            $number,
            $question->type_name,
            Question::DIFFICULTIES[$question->difficulty] ?? ($question->difficulty ?: 'غير محدد'),
            (float) ($question->default_points ?? 0)
        );

        $this->addRtlText($section, $header, ['bold' => true, 'size' => 13, 'color' => '1E3A8A']);

        $stem = QuestionExportContentRenderer::questionStemText($question);
        if ($stem !== '') {
            foreach (preg_split('/\r\n|\r|\n/', $stem) ?: [$stem] as $line) {
                $line = trim($line);
                if ($line !== '') {
                    $this->addRtlText($section, $line);
                }
            }
        }

        $this->addQuestionImage($section, $question->image);

        $options = $question->relationLoaded('options')
            ? $question->options
            : $question->options()->orderBy('order')->get();

        $this->renderQuestionTypeBody($section, $question, $options);

        $this->addTeacherKey($section, $question, $options);

        $section->addTextBreak(1);
        $this->addRtlText($section, str_repeat('—', 40), ['color' => 'CCCCCC', 'size' => 10]);
        $section->addTextBreak(1);
    }

    private function renderQuestionTypeBody(
        \PhpOffice\PhpWord\Element\Section $section,
        Question $question,
        Collection $options
    ): void {
        match ($question->type) {
            'single_choice', 'multiple_choice', 'true_false' => $this->addChoiceOptions($section, $options),
            'matching' => $this->addMatchingTable($section, $options),
            'ordering' => $this->addOrderingList($section, $options),
            'fill_blanks' => $this->addFillBlanks($section, $question),
            'numerical' => $this->addNumericalAnswer($section, $question, $options),
            'short_answer', 'essay' => $this->addOpenAnswerHint($section),
            'drag_drop' => $this->addDragDropAssignments($section, $options),
            default => $this->addRtlText($section, '—', ['italic' => true]),
        };
    }

    private function addChoiceOptions(\PhpOffice\PhpWord\Element\Section $section, Collection $options): void
    {
        foreach ($options->values() as $index => $option) {
            $letter = chr(65 + $index);
            $prefix = $option->is_correct ? '✓ ' : '';
            $text = $prefix.$letter.'. '.QuestionExportContentRenderer::toPlainText($option->content);
            $this->addRtlText($section, $text, $option->is_correct ? ['bold' => true] : []);
            $this->addQuestionImage($section, $option->image, 120);
        }
    }

    private function addMatchingTable(\PhpOffice\PhpWord\Element\Section $section, Collection $options): void
    {
        $table = $section->addTable(['borderSize' => 6, 'borderColor' => 'CCCCCC', 'rtl' => true]);
        $table->addRow();
        $table->addCell(4500)->addText('العنصر', $this->rtlFont(['bold' => true]), $this->rtlParagraph());
        $table->addCell(4500)->addText('الهدف المطابق', $this->rtlFont(['bold' => true]), $this->rtlParagraph());

        foreach ($options as $option) {
            $table->addRow();
            $left = QuestionExportContentRenderer::toPlainText($option->content);
            $right = QuestionExportContentRenderer::toPlainText($option->match_target);
            $style = $option->is_correct ? ['bold' => true] : [];
            $table->addCell(4500)->addText($left, $this->rtlFont($style), $this->rtlParagraph());
            $table->addCell(4500)->addText($right, $this->rtlFont($style), $this->rtlParagraph());
        }
    }

    private function addOrderingList(\PhpOffice\PhpWord\Element\Section $section, Collection $options): void
    {
        $ordered = $options->sortBy(fn ($opt) => $opt->correct_order ?? $opt->order);
        foreach ($ordered->values() as $index => $option) {
            $text = ($index + 1).'. '.QuestionExportContentRenderer::toPlainText($option->content);
            $this->addRtlText($section, $text, $option->is_correct ? ['bold' => true] : []);
        }
    }

    private function addFillBlanks(\PhpOffice\PhpWord\Element\Section $section, Question $question): void
    {
        $answers = is_array($question->blank_answers) ? $question->blank_answers : [];
        if ($answers === []) {
            $this->addRtlText($section, '—', ['italic' => true]);

            return;
        }

        $this->addRtlText($section, 'الإجابات الصحيحة للفراغات:', ['bold' => true]);
        foreach ($answers as $index => $answer) {
            $text = is_array($answer) ? implode(' / ', $answer) : (string) $answer;
            $this->addRtlText($section, ($index + 1).'. '.$text);
        }
    }

    private function addNumericalAnswer(\PhpOffice\PhpWord\Element\Section $section, Question $question, Collection $options): void
    {
        $correct = $options->firstWhere('is_correct', true);
        if ($correct) {
            $this->addRtlText($section, 'القيمة الصحيحة: '.QuestionExportContentRenderer::toPlainText($correct->content), ['bold' => true]);
        }
        if ($question->tolerance !== null && $question->tolerance !== '') {
            $this->addRtlText($section, 'هامش التسامح: '.$question->tolerance);
        }
    }

    private function addOpenAnswerHint(\PhpOffice\PhpWord\Element\Section $section): void
    {
        $this->addRtlText($section, 'إجابة نموذجية / مساحة للإجابة:', ['italic' => true, 'color' => '666666']);
        $section->addTextBreak(2);
    }

    private function addDragDropAssignments(\PhpOffice\PhpWord\Element\Section $section, Collection $options): void
    {
        foreach ($options as $option) {
            $zone = QuestionExportContentRenderer::toPlainText($option->match_target) ?: 'منطقة';
            $item = QuestionExportContentRenderer::toPlainText($option->content);
            $this->addRtlText($section, $zone.' ← '.$item, $option->is_correct ? ['bold' => true] : []);
        }
    }

    private function addTeacherKey(\PhpOffice\PhpWord\Element\Section $section, Question $question, Collection $options): void
    {
        $section->addTextBreak(1);
        $this->addRtlText($section, 'الإجابة الصحيحة:', ['bold' => true, 'color' => '047857']);

        $answer = $this->resolveCorrectAnswerText($question, $options);
        if ($answer !== '') {
            $this->addRtlText($section, $answer, ['bold' => true]);
        }

        $explanation = QuestionExportContentRenderer::toPlainText($question->explanation);
        if ($explanation !== '') {
            $this->addRtlText($section, 'الشرح:', ['bold' => true, 'color' => '1D4ED8']);
            $this->addRtlText($section, $explanation);
        }
    }

    private function resolveCorrectAnswerText(Question $question, Collection $options): string
    {
        return match ($question->type) {
            'single_choice', 'multiple_choice', 'true_false', 'numerical' => $options
                ->where('is_correct', true)
                ->map(fn ($opt) => QuestionExportContentRenderer::toPlainText($opt->content))
                ->filter()
                ->implode('، '),
            'matching' => $options
                ->map(fn ($opt) => QuestionExportContentRenderer::toPlainText($opt->content)
                    .' → '
                    .QuestionExportContentRenderer::toPlainText($opt->match_target))
                ->implode(' | '),
            'ordering' => $options
                ->sortBy(fn ($opt) => $opt->correct_order ?? $opt->order)
                ->values()
                ->map(fn ($opt, $i) => ($i + 1).'. '.QuestionExportContentRenderer::toPlainText($opt->content))
                ->implode(' — '),
            'fill_blanks' => collect(is_array($question->blank_answers) ? $question->blank_answers : [])
                ->map(fn ($a, $i) => ($i + 1).'. '.(is_array($a) ? implode(' / ', $a) : (string) $a))
                ->implode(' — '),
            'drag_drop' => $options
                ->map(fn ($opt) => (QuestionExportContentRenderer::toPlainText($opt->match_target) ?: 'منطقة')
                    .': '
                    .QuestionExportContentRenderer::toPlainText($opt->content))
                ->implode(' | '),
            default => QuestionExportContentRenderer::toPlainText($question->explanation),
        };
    }

    private function addQuestionImage(\PhpOffice\PhpWord\Element\Section $section, ?string $image, int $width = 220): void
    {
        $path = QuestionExportContentRenderer::localImagePath($image);
        if ($path === null) {
            return;
        }

        try {
            $section->addImage($path, [
                'width' => $width,
                'alignment' => Jc::END,
                'rtl' => true,
            ]);
            $section->addTextBreak(1);
        } catch (\Throwable) {
            // تجاهل الصور غير القابلة للقراءة محلياً
        }
    }

    /**
     * @param  array<string, mixed>  $font
     */
    private function addRtlText(\PhpOffice\PhpWord\Element\Section $section, string $text, array $font = []): void
    {
        $section->addText($text, $this->rtlFont($font), $this->rtlParagraph());
    }

    /**
     * @param  array<string, mixed>  $extra
     * @return array<string, mixed>
     */
    private function rtlFont(array $extra = []): array
    {
        return array_merge([
            'name' => self::FONT,
            'size' => 12,
            'rtl' => true,
        ], $extra);
    }

    /**
     * @return array<string, mixed>
     */
    private function rtlParagraph(): array
    {
        return [
            'alignment' => Jc::END,
            'rtl' => true,
        ];
    }
}
