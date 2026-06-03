<?php

namespace App\Services\NerveTestImport;

use App\DataTransferObjects\NerveTest\NerveTestQuestionData;
use Illuminate\Http\UploadedFile;

class NerveTestParserFactory
{
    public function __construct(
        protected MarkdownNerveTestParser $markdownParser = new MarkdownNerveTestParser,
        protected CsvNerveTestParser $csvParser = new CsvNerveTestParser,
    ) {}

    /**
     * @return array<int, NerveTestQuestionData>
     */
    public function parseUploadedFile(UploadedFile $file, string $format): array
    {
        $content = file_get_contents($file->getRealPath());
        if ($content === false) {
            throw new NerveTestParseException('تعذر قراءة الملف.');
        }

        return $this->parseContent($content, $format);
    }

    /**
     * @return array<int, NerveTestQuestionData>
     */
    public function parseContent(string $content, string $format): array
    {
        return match ($format) {
            'md', 'markdown' => $this->markdownParser->parse($content),
            'csv' => $this->csvParser->parse($content),
            default => throw new NerveTestParseException('صيغة الملف غير مدعومة.'),
        };
    }
}
