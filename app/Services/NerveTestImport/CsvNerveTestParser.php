<?php

namespace App\Services\NerveTestImport;

use App\DataTransferObjects\NerveTest\NerveTestQuestionData;

class CsvNerveTestParser
{
    /**
     * @return array<int, NerveTestQuestionData>
     */
    public function parse(string $content): array
    {
        $content = preg_replace('/^\xEF\xBB\xBF/', '', $content) ?? $content;
        $content = trim($content);

        if ($content === '') {
            throw new NerveTestParseException('الملف فارغ.');
        }

        $handle = fopen('php://memory', 'r+');
        if ($handle === false) {
            throw new NerveTestParseException('تعذر قراءة الملف.');
        }

        fwrite($handle, $content);
        rewind($handle);

        $header = fgetcsv($handle);
        if ($header === false) {
            fclose($handle);
            throw new NerveTestParseException('رأس الجدول مفقود.');
        }

        $header = array_map(fn ($col) => trim(trim($col, '"')), $header);
        $columnMap = $this->mapColumns($header);

        $questions = [];
        $rowNumber = 1;

        while (($row = fgetcsv($handle)) !== false) {
            $rowNumber++;
            if ($this->isEmptyRow($row)) {
                continue;
            }

            $data = [];
            foreach ($columnMap as $key => $index) {
                $data[$key] = trim(trim($row[$index] ?? '', '"'));
            }

            $questions[] = $this->buildQuestion($data, (int) ($data['number'] ?: count($questions) + 1));
        }

        fclose($handle);

        if ($questions === []) {
            throw new NerveTestParseException('لم يُستخرج أي سؤال من الملف.');
        }

        return $questions;
    }

    /**
     * @param  array<string, string>  $data
     */
    protected function buildQuestion(array $data, int $number): NerveTestQuestionData
    {
        $title = $data['question'] ?? '';
        if ($title === '') {
            throw new NerveTestParseException("الصف {$number}: عنوان السؤال مفقود.");
        }

        $optionA = $data['option_a'] ?? '';
        $optionB = $data['option_b'] ?? '';
        if ($optionA === '' || $optionB === '') {
            throw new NerveTestParseException("الصف {$number}: خيارات A/B غير مكتملة.");
        }

        $correctLetter = $this->parseCorrectLetter($data['correct_answer'] ?? '', $number);

        return new NerveTestQuestionData(
            number: $number,
            title: $title,
            hint: $data['hint'] ?? '',
            optionA: $optionA,
            optionB: $optionB,
            correctLetter: $correctLetter,
            explanation: $data['rationale'] ?? '',
        );
    }

    protected function parseCorrectLetter(string $value, int $number): string
    {
        if (preg_match('/\b([AB])\b/iu', $value, $m)) {
            return strtoupper($m[1]);
        }

        throw new NerveTestParseException("الصف {$number}: صيغة الإجابة الصحيحة غير معروفة ({$value}).");
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
            'option_a' => ['option a', 'option_a', 'الخيار الأول'],
            'option_b' => ['option b', 'option_b', 'الخيار الثاني'],
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
                throw new NerveTestParseException("عمود مطلوب مفقود: {$key}");
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
