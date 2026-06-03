<?php

namespace App\DataTransferObjects\NerveTest;

readonly class NerveTestQuestionData
{
    public function __construct(
        public int $number,
        public string $title,
        public string $hint,
        public string $optionA,
        public string $optionB,
        public string $correctLetter,
        public string $explanation,
        public string $type = 'true_false',
        public string $difficulty = 'medium',
        public float $points = 1.0,
    ) {}

    public function correctOptionContent(): string
    {
        return strtoupper($this->correctLetter) === 'B' ? $this->optionB : $this->optionA;
    }

    public function toPreviewArray(): array
    {
        return [
            'number' => $this->number,
            'title' => $this->title,
            'hint' => $this->hint,
            'option_a' => $this->optionA,
            'option_b' => $this->optionB,
            'correct_answer' => $this->correctLetter.'. '.$this->correctOptionContent(),
            'explanation' => $this->explanation,
            'type' => $this->type,
            'difficulty' => $this->difficulty,
            'points' => $this->points,
        ];
    }
}
