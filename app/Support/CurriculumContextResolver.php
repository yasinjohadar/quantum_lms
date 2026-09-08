<?php

namespace App\Support;

use App\Models\Lesson;
use App\Models\SchoolClass;
use App\Models\Subject;
use App\Models\SubjectSection;
use App\Models\Unit;
use Illuminate\Database\Eloquent\Model;

/**
 * يحدّد "الصف" و"المادة" اللذين ينتمي إليهما عنصر منهج معيّن (قسم/وحدة/درس/اختبار)
 * بالصعود عبر السلسلة الهرمية. يُستخدم من AuditableObserver وقت وقوع الحدث فقط
 * (النتيجة تُخزَّن كلقطة في audit_logs، فلا كلفة إضافية عند عرض السجل لاحقاً).
 *
 * يستخدم withTrashed() لأن أحد الأسلاف قد يكون محذوفاً مؤقتاً وقت وقوع الحدث.
 */
class CurriculumContextResolver
{
    public function resolve(Model $model, string $subjectKey): array
    {
        return match ($subjectKey) {
            'class' => ['class_id' => $model->getKey(), 'class_name' => $model->name],
            'subject' => array_merge(
                ['curriculum_subject_id' => $model->getKey(), 'curriculum_subject_name' => $model->name],
                $this->contextFromClassId($model->class_id ?? null)
            ),
            'subject_section' => $this->contextFromSubjectId($model->subject_id ?? null),
            'unit' => $this->contextFromSectionId($model->section_id ?? null),
            'lesson' => ($model->unit_id ?? null)
                ? $this->contextFromUnitId($model->unit_id)
                : $this->contextFromSectionId($model->section_id ?? null),
            'quiz' => $this->quizContext($model),
            default => [],
        };
    }

    private function quizContext(Model $quiz): array
    {
        if ($quiz->lesson_id ?? null) {
            $lesson = Lesson::withTrashed()->find($quiz->lesson_id);
            if ($lesson) {
                return $lesson->unit_id
                    ? $this->contextFromUnitId($lesson->unit_id)
                    : $this->contextFromSectionId($lesson->section_id);
            }
        }
        if ($quiz->unit_id ?? null) {
            return $this->contextFromUnitId($quiz->unit_id);
        }
        if ($quiz->section_id ?? null) {
            return $this->contextFromSectionId($quiz->section_id);
        }
        if ($quiz->subject_id ?? null) {
            return $this->contextFromSubjectId($quiz->subject_id);
        }

        return [];
    }

    private function contextFromUnitId(?int $unitId): array
    {
        if (! $unitId) {
            return [];
        }
        $unit = Unit::withTrashed()->find($unitId);

        return $unit ? $this->contextFromSectionId($unit->section_id) : [];
    }

    private function contextFromSectionId(?int $sectionId): array
    {
        if (! $sectionId) {
            return [];
        }
        $section = SubjectSection::withTrashed()->find($sectionId);

        return $section ? $this->contextFromSubjectId($section->subject_id) : [];
    }

    private function contextFromSubjectId(?int $subjectId): array
    {
        if (! $subjectId) {
            return [];
        }
        $subject = Subject::withTrashed()->find($subjectId);
        if (! $subject) {
            return [];
        }

        return array_merge(
            ['curriculum_subject_id' => $subject->id, 'curriculum_subject_name' => $subject->name],
            $this->contextFromClassId($subject->class_id)
        );
    }

    private function contextFromClassId(?int $classId): array
    {
        if (! $classId) {
            return [];
        }
        $class = SchoolClass::withTrashed()->find($classId);

        return $class ? ['class_id' => $class->id, 'class_name' => $class->name] : [];
    }
}
