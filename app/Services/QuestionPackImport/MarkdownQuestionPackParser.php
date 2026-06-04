<?php

namespace App\Services\QuestionPackImport;

use App\DataTransferObjects\QuestionPack\QuestionPackQuestionData;
use App\Services\QuestionPackImport\Concerns\ParsesQuestionPackAnswers;

class MarkdownQuestionPackParser
{
    use ParsesQuestionPackAnswers;

    /**
     * @return array<int, QuestionPackQuestionData>
     */
    public function parse(string $content, string $targetType): array
    {
        $content = trim($content);
        if ($content === '') {
            throw new QuestionPackParseException('الملف فارغ.');
        }

        $blocks = preg_split('/^##\s+\d+\.\s*/mu', $content, -1, PREG_SPLIT_NO_EMPTY);
        if ($blocks === false || $blocks === []) {
            throw new QuestionPackParseException('لم يُعثر على أسئلة بصيغة ## رقم.');
        }

        $questions = [];
        $expectedNumber = 1;

        foreach ($blocks as $block) {
            $block = trim($block);
            if ($block === '' || ! preg_match('/-\s*\*\*A\.\*\*/u', $block)) {
                continue;
            }

            $questions[] = $this->parseBlock($block, $expectedNumber, $targetType);
            $expectedNumber++;
        }

        if ($questions === []) {
            throw new QuestionPackParseException('لم يُستخرج أي سؤال من الملف.');
        }

        return $questions;
    }

    protected function parseBlock(string $block, int $number, string $targetType): QuestionPackQuestionData
    {
        $lines = preg_split('/\r\n|\r|\n/', $block) ?: [];
        $title = trim($lines[0] ?? '');
        if ($title === '') {
            throw new QuestionPackParseException("السؤال رقم {$number}: العنوان مفقود.");
        }

        $hint = '';
        $options = [];
        $correctLetter = '';
        $explanation = '';
        $inRationale = false;
        $rationaleLines = [];

        foreach ($lines as $index => $line) {
            if ($index === 0) {
                continue;
            }

            $trimmed = trim($line);

            if (preg_match('/^\>\s*💡\s*\*\*Hint:\*\*\s*(.+)$/u', $trimmed, $m)) {
                $hint = trim($m[1]);

                continue;
            }

            if (preg_match('/^-\s*\*\*([A-D])\.\*\*\s*(.+)$/iu', $trimmed, $m)) {
                $options[strtoupper($m[1])] = trim($m[2]);

                continue;
            }

            if (preg_match('/^\>\s*\*\*Answer:\*\*\s*([A-D])\s*$/iu', $trimmed, $m)) {
                $correctLetter = strtoupper($m[1]);
                $inRationale = false;

                continue;
            }

            if (preg_match('/^\>\s*\*\*Rationale:\*\*\s*(.*)$/u', $trimmed, $m)) {
                $inRationale = true;
                $rationaleLines = [trim($m[1])];

                continue;
            }

            if ($inRationale && preg_match('/^\>\s*(.*)$/u', $trimmed, $m)) {
                $rationaleLines[] = trim($m[1]);
            }
        }

        $explanation = trim(implode(' ', array_filter($rationaleLines)));

        if ($correctLetter === '') {
            throw new QuestionPackParseException("السؤال رقم {$number}: الإجابة الصحيحة غير محددة.");
        }

        $this->validateOptions($options, $correctLetter, $number);

        return new QuestionPackQuestionData(
            number: $number,
            title: $title,
            hint: $hint,
            options: $options,
            correctLetter: $correctLetter,
            explanation: $explanation,
            targetType: $targetType,
        );
    }
}
