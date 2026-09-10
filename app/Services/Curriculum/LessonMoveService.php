<?php

namespace App\Services\Curriculum;

use App\Models\Lesson;
use App\Models\Quiz;
use App\Models\SubjectSection;
use App\Models\Unit;
use Illuminate\Support\Facades\DB;

class LessonMoveService
{
    /**
     * التأكد أن الدرس (وكل اختباراته) غير مرتبط بأي وحدة/مادة أخرى عبر أي آلية ربط/مزامنة
     * قديمة أو حديثة، قبل السماح بنقله فعليًا لوحدة/قسم آخر.
     *
     * @throws \InvalidArgumentException
     */
    public function assertMovable(Lesson $lesson): void
    {
        if ($lesson->sync_group_id !== null || $lesson->cloned_from_lesson_id !== null) {
            throw new \InvalidArgumentException(
                'لا يمكن نقل هذا الدرس لأنه مرتبط حالياً بمواد أخرى. '
                .'يرجى إلغاء الربط أولاً ثم إعادة المحاولة.'
            );
        }

        $quizIds = Quiz::query()->where('lesson_id', $lesson->id)->pluck('id')->all();

        $legacyLessonUnitLinksCount = DB::table('lesson_units')
            ->where('lesson_id', $lesson->id)
            ->count();

        if ($legacyLessonUnitLinksCount > 0) {
            throw new \InvalidArgumentException(
                'لا يمكن نقل هذا الدرس لأنه مرتبط بوحدة أخرى (ربط قديم). '
                .'يرجى إلغاء هذا الربط أولاً ثم إعادة المحاولة.'
            );
        }

        $legacyQuizUnitLinksCount = empty($quizIds)
            ? 0
            : DB::table('quiz_units')->whereIn('quiz_id', $quizIds)->count();

        if ($legacyQuizUnitLinksCount > 0) {
            throw new \InvalidArgumentException(
                'لا يمكن نقل هذا الدرس لأن أحد اختباراته مرتبط بوحدة أخرى (ربط قديم). '
                .'يرجى إلغاء هذا الربط أولاً ثم إعادة المحاولة.'
            );
        }
    }

    /**
     * نقل الدرس (وكل اختباراته) فعلياً إلى قسم/وحدة أخرى (قد تكون في مادة مختلفة).
     * إذا لم تُحدَّد وحدة هدف، يصبح الدرس درساً مباشراً في القسم الهدف.
     */
    public function moveLessonTo(
        Lesson $lesson,
        SubjectSection $targetSection,
        ?Unit $targetUnit = null
    ): Lesson {
        if ($targetUnit && (int) $targetUnit->section_id !== (int) $targetSection->id) {
            throw new \InvalidArgumentException('الوحدة الهدف يجب أن تنتمي للقسم الهدف.');
        }

        $sameParent = $targetUnit
            ? (int) $lesson->unit_id === (int) $targetUnit->id
            : ($lesson->unit_id === null && (int) $lesson->section_id === (int) $targetSection->id);

        if ($sameParent) {
            throw new \InvalidArgumentException(
                'الدرس موجود بالفعل في هذا المكان. لتغيير مكانه استخدم زر تعديل الدرس.'
            );
        }

        return DB::transaction(function () use ($lesson, $targetSection, $targetUnit) {
            $newOrder = ($targetUnit
                ? Lesson::query()->where('unit_id', $targetUnit->id)->max('order')
                : Lesson::query()->whereNull('unit_id')->where('section_id', $targetSection->id)->max('order')
            ) ?? 0;
            $newOrder += 1;

            Lesson::query()
                ->where('id', $lesson->id)
                ->update([
                    'unit_id' => $targetUnit?->id,
                    'section_id' => $targetSection->id,
                    'order' => $newOrder,
                ]);

            Quiz::query()
                ->where('lesson_id', $lesson->id)
                ->update([
                    'unit_id' => $targetUnit?->id,
                    'section_id' => $targetSection->id,
                    'subject_id' => $targetSection->subject_id,
                ]);

            return $lesson->fresh();
        });
    }
}
