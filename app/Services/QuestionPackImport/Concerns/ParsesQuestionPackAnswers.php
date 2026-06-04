<?php

namespace App\Services\QuestionPackImport\Concerns;

use App\Services\QuestionPackImport\QuestionPackParseException;

trait ParsesQuestionPackAnswers
{
    protected function parseCorrectLetter(string $value, int $number): string
    {
        if (preg_match('/\b([A-D])\b/iu', $value, $m)) {
            return strtoupper($m[1]);
        }

        throw new QuestionPackParseException("السؤال/الصف {$number}: صيغة الإجابة الصحيحة غير معروفة ({$value}).");
    }

    /**
     * @param  array<string, string>  $options
     */
    protected function validateOptions(array $options, string $correctLetter, int $number): void
    {
        if (count($options) < 2) {
            throw new QuestionPackParseException("السؤال/الصف {$number}: يجب توفير خيارين على الأقل (A–D).");
        }

        if (! isset($options[$correctLetter])) {
            throw new QuestionPackParseException("السؤال/الصف {$number}: الخيار الصحيح «{$correctLetter}» غير موجود.");
        }
    }
}
