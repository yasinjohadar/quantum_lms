<?php

namespace App\Support;

use App\Models\Subject;
use App\Models\Unit;

class QuestionImportCurriculumValidator
{
    public static function validate(?string $classId, ?string $subjectId, ?string $unitId): ?string
    {
        if ($unitId && ! $subjectId) {
            return 'يجب اختيار المادة عند تحديد الوحدة.';
        }

        if ($subjectId) {
            $subject = Subject::find($subjectId);
            if (! $subject) {
                return 'المادة المحددة غير صالحة.';
            }
            if ($classId && (int) $subject->class_id !== (int) $classId) {
                return 'المادة لا تنتمي للصف المحدد.';
            }
        }

        if ($unitId) {
            $belongsToSubject = Unit::whereKey($unitId)
                ->whereHas('section', fn ($q) => $q->where('subject_id', $subjectId))
                ->exists();
            if (! $belongsToSubject) {
                return 'الوحدة لا تنتمي للمادة المحددة.';
            }
        }

        return null;
    }
}
