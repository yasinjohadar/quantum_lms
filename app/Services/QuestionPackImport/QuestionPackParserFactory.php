<?php

namespace App\Services\QuestionPackImport;

use App\DataTransferObjects\QuestionPack\QuestionPackQuestionData;
use Illuminate\Http\UploadedFile;

class QuestionPackParserFactory
{
    public function __construct(
        protected MarkdownQuestionPackParser $markdownParser = new MarkdownQuestionPackParser,
        protected CsvQuestionPackParser $csvParser = new CsvQuestionPackParser,
    ) {}

    /**
     * @return array<int, QuestionPackQuestionData>
     */
    public function parseUploadedFile(UploadedFile $file, string $format, string $targetType): array
    {
        $content = file_get_contents($file->getRealPath());
        if ($content === false) {
            throw new QuestionPackParseException('تعذر قراءة الملف.');
        }

        return $this->parseContent($content, $format, $targetType);
    }

    /**
     * @return array<int, QuestionPackQuestionData>
     */
    public function parseContent(string $content, string $format, string $targetType): array
    {
        $this->assertTargetType($targetType);

        return match ($format) {
            'md', 'markdown' => $this->markdownParser->parse($content, $targetType),
            'csv' => $this->csvParser->parse($content, $targetType),
            default => throw new QuestionPackParseException('صيغة الملف غير مدعومة.'),
        };
    }

    protected function assertTargetType(string $targetType): void
    {
        if (! in_array($targetType, ['single_choice', 'fill_blanks'], true)) {
            throw new QuestionPackParseException('نوع الحفظ غير مدعوم.');
        }
    }
}
