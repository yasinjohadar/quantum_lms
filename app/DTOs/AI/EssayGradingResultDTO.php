<?php

namespace App\DTOs\AI;

class EssayGradingResultDTO
{
    public function __construct(
        public float $points,
        public float $max_points,
        public array $criteria_scores = [],
        public string $feedback = '',
        public array $strengths = [],
        public array $weaknesses = [],
        public array $suggestions = [],
    ) {}

    /**
     * حساب النسبة المئوية
     */
    public function getPercentage(): float
    {
        return $this->max_points > 0 ? ($this->points / $this->max_points) * 100 : 0;
    }

    /**
     * التحويل إلى array
     */
    public function toArray(): array
    {
        return [
            'points' => $this->points,
            'max_points' => $this->max_points,
            'percentage' => $this->getPercentage(),
            'criteria_scores' => $this->criteria_scores,
            'feedback' => $this->feedback,
            'strengths' => $this->strengths,
            'weaknesses' => $this->weaknesses,
            'suggestions' => $this->suggestions,
        ];
    }
}


