<?php

namespace App\Services\NerveTestImport;

use App\DataTransferObjects\NerveTest\NerveTestQuestionData;

class MarkdownNerveTestParser
{
    /**
     * @return array<int, NerveTestQuestionData>
     */
    public function parse(string $content): array
    {
        $content = trim($content);
        if ($content === '') {
            throw new NerveTestParseException('الملف فارغ.');
        }

        $blocks = preg_split('/^##\s+\d+\.\s*/mu', $content, -1, PREG_SPLIT_NO_EMPTY);
        if ($blocks === false || $blocks === []) {
            throw new NerveTestParseException('لم يُعثر على أسئلة بصيغة ## رقم.');
        }

        $questions = [];
        $expectedNumber = 1;

        foreach ($blocks as $block) {
            $block = trim($block);
            if ($block === '' || ! preg_match('/-\s*\*\*A\.\*\*/u', $block)) {
                continue;
            }

            $question = $this->parseBlock($block, $expectedNumber);
            $questions[] = $question;
            $expectedNumber++;
        }

        if ($questions === []) {
            throw new NerveTestParseException('لم يُستخرج أي سؤال من الملف.');
        }

        return $questions;
    }

    protected function parseBlock(string $block, int $number): NerveTestQuestionData
    {
        $lines = preg_split('/\r\n|\r|\n/', $block) ?: [];
        $title = trim($lines[0] ?? '');
        if ($title === '') {
            throw new NerveTestParseException("السؤال رقم {$number}: العنوان مفقود.");
        }

        $hint = '';
        $optionA = '';
        $optionB = '';
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

            if (preg_match('/^-\s*\*\*A\.\*\*\s*(.+)$/u', $trimmed, $m)) {
                $optionA = trim($m[1]);

                continue;
            }

            if (preg_match('/^-\s*\*\*B\.\*\*\s*(.+)$/u', $trimmed, $m)) {
                $optionB = trim($m[1]);

                continue;
            }

            if (preg_match('/^\>\s*\*\*Answer:\*\*\s*([AB])\s*$/iu', $trimmed, $m)) {
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

        if ($optionA === '' || $optionB === '') {
            throw new NerveTestParseException("السؤال رقم {$number}: خيارات A/B غير مكتملة.");
        }

        if (! in_array($correctLetter, ['A', 'B'], true)) {
            throw new NerveTestParseException("السؤال رقم {$number}: الإجابة الصحيحة غير محددة.");
        }

        return new NerveTestQuestionData(
            number: $number,
            title: $title,
            hint: $hint,
            optionA: $optionA,
            optionB: $optionB,
            correctLetter: $correctLetter,
            explanation: $explanation,
        );
    }
}
