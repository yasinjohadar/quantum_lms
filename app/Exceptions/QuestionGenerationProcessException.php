<?php

namespace App\Exceptions;

use App\Models\AIQuestionGeneration;
use Exception;

class QuestionGenerationProcessException extends Exception
{
    public function __construct(
        public readonly AIQuestionGeneration $generation,
        string $message,
        ?\Throwable $previous = null
    ) {
        parent::__construct($message, 0, $previous);
    }
}
