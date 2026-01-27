<?php

namespace App\Helpers;

use App\Models\User;

class TeacherHelper
{
    /**
     * التحقق من أن المعلم يمكنه الوصول إلى مادة معينة
     */
    public static function canAccessSubject(User $teacher, $subjectId)
    {
        if (!$teacher->hasRole('teacher')) {
            return false;
        }

        $subject = \App\Models\Subject::find($subjectId);
        if (!$subject) {
            return false;
        }

        // إذا كان مسؤول عن الصف، يمكنه الوصول
        if ($teacher->isAssignedToClass($subject->class_id)) {
            return true;
        }

        // إذا كان مسؤول عن المادة مباشرة
        if ($teacher->isAssignedToSubject($subjectId)) {
            return true;
        }

        return false;
    }

    /**
     * التحقق من أن المعلم يمكنه الوصول إلى صف معين
     */
    public static function canAccessClass(User $teacher, $classId)
    {
        if (!$teacher->hasRole('teacher')) {
            return false;
        }

        return $teacher->isAssignedToClass($classId);
    }
}
