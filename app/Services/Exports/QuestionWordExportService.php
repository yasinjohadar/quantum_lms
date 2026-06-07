<?php

namespace App\Services\Exports;

use App\Models\Question;
use App\Models\Subject;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection;

class QuestionWordExportService
{
    public const MAX_QUESTIONS = 500;

    public function __construct(
        private readonly QuestionWordDocumentBuilder $builder
    ) {}

    /**
     * @param  array<int>  $selectedIds
     * @param  array{title: string, subtitle?: string, note?: string}  $meta
     */
    public function exportFromQuery(
        Builder $query,
        string $scope,
        array $selectedIds,
        string $order,
        array $meta
    ): string {
        if ($scope === 'selected') {
            $query->whereIn('id', array_values(array_unique($selectedIds)));
        }

        $questions = $query
            ->with(['options', 'subject.schoolClass'])
            ->get();

        if ($questions->isEmpty()) {
            throw new QuestionWordExportException('لا توجد أسئلة مطابقة للتصدير.');
        }

        if ($questions->count() > self::MAX_QUESTIONS) {
            throw new QuestionWordExportException(
                'لا يمكن تصدير أكثر من '.self::MAX_QUESTIONS.' سؤال دفعة واحدة. قلّل النطاق أو الفلاتر.'
            );
        }

        return $this->builder->saveToTempFile($questions, $meta, $order);
    }

    /**
     * @return array{title: string, subtitle?: string}
     */
    public function buildDocumentMeta(?Subject $subject, int $questionCount): array
    {
        if ($subject !== null) {
            $subject->loadMissing('schoolClass.stage');
            $subtitleParts = array_filter([
                $subject->schoolClass?->name,
                $subject->schoolClass?->stage?->name,
            ]);

            return [
                'title' => 'بنك أسئلة — '.$subject->name,
                'subtitle' => $subtitleParts !== [] ? implode(' — ', $subtitleParts) : null,
                'note' => 'مفتاح معلم — يتضمن الإجابات الصحيحة والشرح',
            ];
        }

        return [
            'title' => 'بنك الأسئلة الرئيسي',
            'subtitle' => 'عدد الأسئلة المُصدَّرة: '.$questionCount,
            'note' => 'مفتاح معلم — يتضمن الإجابات الصحيحة والشرح',
        ];
    }

    public function downloadFilename(?Subject $subject): string
    {
        $date = now()->format('Y-m-d');
        if ($subject !== null) {
            $slug = preg_replace('/[^\p{L}\p{N}\-]+/u', '-', $subject->name) ?? 'subject';
            $slug = trim($slug, '-') ?: 'subject';

            return 'بنك-أسئلة-'.$slug.'-'.$date.'.docx';
        }

        return 'بنك-أسئلة-'.$date.'.docx';
    }
}
