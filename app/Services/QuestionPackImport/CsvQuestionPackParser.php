<?php

namespace App\Services\QuestionPackImport;

use App\DataTransferObjects\QuestionPack\QuestionPackQuestionData;
use App\Services\QuestionPackImport\Concerns\ParsesQuestionPackAnswers;

class CsvQuestionPackParser
{
    use ParsesQuestionPackAnswers;

    /**
     * @return array<int, QuestionPackQuestionData>
     */
    public function parse(string $content, string $targetType): array
    {
        $content = preg_replace('/^\xEF\xBB\xBF/', '', $content) ?? $content;
        $content = trim($content);

        if ($content === '') {
            throw new QuestionPackParseException('الملف فارغ.');
        }

        $handle = fopen('php://memory', 'r+');
        if ($handle === false) {
            throw new QuestionPackParseException('تعذر قراءة الملف.');
        }

        fwrite($handle, $content);
        rewind($handle);

        $header = fgetcsv($handle);
        if ($header === false) {
            fclose($handle);
            throw new QuestionPackParseException('رأس الجدول مفقود.');
        }

        $header = array_map(fn ($col) => trim(trim($col, '"')), $header);
        $columnMap = $this->mapColumns($header);

        $questions = [];

        while (($row = fgetcsv($handle)) !== false) {
            if ($this->isEmptyRow($row)) {
                continue;
            }

            $data = [];
            foreach ($columnMap as $key => $index) {
                if ($index < 0) {
                    continue;
                }
                $data[$key] = trim(trim($row[$index] ?? '', '"'));
            }

            $number = (int) ($data['number'] ?? 0) ?: count($questions) + 1;
            $questions[] = $this->buildQuestion($data, $number, $targetType);
        }

        fclose($handle);

        if ($questions === []) {
            throw new QuestionPackParseException('لم يُستخرج أي سؤال من الملف.');
        }

        return $questions;
    }

    /**
     * @param  array<string, string>  $data
     */
    protected function buildQuestion(array $data, int $number, string $targetType): QuestionPackQuestionData
    {
        $title = $data['question'] ?? '';
        if ($title === '') {
            throw new QuestionPackParseException("الصف {$number}: عنوان السؤال مفقود.");
        }

        $options = [];
        foreach (['A', 'B', 'C', 'D'] as $letter) {
            $key = 'option_'.strtolower($letter);
            $value = trim($data[$key] ?? '');
            if ($value !== '') {
                $options[$letter] = $value;
            }
        }

        $correctLetter = $this->parseCorrectLetter($data['correct_answer'] ?? '', $number);
        $this->validateOptions($options, $correctLetter, $number);

        return new QuestionPackQuestionData(
            number: $number,
            title: $title,
            hint: $data['hint'] ?? '',
            options: $options,
            correctLetter: $correctLetter,
            explanation: $data['rationale'] ?? '',
            targetType: $targetType,
        );
    }

    /**
     * @param  array<int, string>  $header
     * @return array<string, int>
     */
    protected function mapColumns(array $header): array
    {
        $aliases = [
            'number' => ['#', 'number', 'رقم'],
            'question' => ['question', 'السؤال', 'عنوان'],
            'hint' => ['hint', 'تلميح'],
            'option_a' => ['option a', 'option_a'],
            'option_b' => ['option b', 'option_b'],
            'option_c' => ['option c', 'option_c'],
            'option_d' => ['option d', 'option_d'],
            'correct_answer' => ['correct answer', 'correct_answer', 'الإجابة'],
            'rationale' => ['rationale', 'شرح', 'التبرير'],
        ];

        $map = [];
        foreach ($header as $index => $col) {
            $normalized = strtolower($col);
            foreach ($aliases as $key => $names) {
                if (in_array($normalized, $names, true) || $normalized === strtolower($key)) {
                    $map[$key] = $index;
                }
            }
        }

        $required = ['question', 'option_a', 'option_b', 'correct_answer'];
        foreach ($required as $key) {
            if (! isset($map[$key])) {
                throw new QuestionPackParseException("عمود مطلوب مفقود: {$key}");
            }
        }

        if (! isset($map['number'])) {
            $map['number'] = -1;
        }

        return $map;
    }

    /**
     * @param  array<int, string|null>  $row
     */
    protected function isEmptyRow(array $row): bool
    {
        foreach ($row as $cell) {
            if (trim((string) $cell) !== '') {
                return false;
            }
        }

        return true;
    }
}
