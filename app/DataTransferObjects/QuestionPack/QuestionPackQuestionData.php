<?php

namespace App\DataTransferObjects\QuestionPack;

readonly class QuestionPackQuestionData
{
    /**
     * @param  array<string, string>  $options  مفاتيح A–D
     */
    public function __construct(
        public int $number,
        public string $title,
        public string $hint,
        public array $options,
        public string $correctLetter,
        public string $explanation,
        public string $targetType,
        public string $difficulty = 'medium',
        public float $points = 1.0,
    ) {}

    public function resolvedType(): string
    {
        return $this->targetType;
    }

    /**
     * @return array<int, string>
     */
    public function blankAnswers(): array
    {
        $letter = strtoupper($this->correctLetter);
        $text = trim($this->options[$letter] ?? '');

        return $text !== '' ? [$text] : [];
    }

    public function hasBlankPlaceholder(): bool
    {
        return (bool) preg_match('/\.{3,}/u', $this->title);
    }

    public function correctOptionContent(): string
    {
        return trim($this->options[strtoupper($this->correctLetter)] ?? '');
    }

    public function toPreviewArray(): array
    {
        $warnings = [];
        if ($this->targetType === 'fill_blanks' && ! $this->hasBlankPlaceholder()) {
            $warnings[] = 'لا يحتوي العنوان على نقاط (..........) — سيُستخدم نص الخيار الصحيح كإجابة للفراغ.';
        }

        return [
            'number' => $this->number,
            'title' => $this->title,
            'hint' => $this->hint,
            'options' => $this->options,
            'options_summary' => $this->optionsSummary(),
            'correct_answer' => $this->correctLetter.'. '.$this->correctOptionContent(),
            'blank_answer' => $this->targetType === 'fill_blanks' ? ($this->blankAnswers()[0] ?? '') : null,
            'explanation' => $this->explanation,
            'type' => $this->targetType,
            'type_label' => $this->targetType === 'fill_blanks' ? 'املأ الفراغ' : 'اختيار من متعدد',
            'difficulty' => $this->difficulty,
            'points' => $this->points,
            'warnings' => $warnings,
        ];
    }

    protected function optionsSummary(): string
    {
        if ($this->targetType === 'fill_blanks') {
            return '—';
        }

        $parts = [];
        foreach (['A', 'B', 'C', 'D'] as $letter) {
            if (! empty($this->options[$letter])) {
                $parts[] = $letter.': '.$this->options[$letter];
            }
        }

        return implode(' | ', $parts);
    }
}
