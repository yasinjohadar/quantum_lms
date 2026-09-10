<?php

namespace App\Services\Curriculum;

use App\Models\Lesson;
use App\Models\Quiz;
use App\Models\SubjectSection;
use App\Models\Unit;
use Illuminate\Support\Facades\DB;

class UnitMoveService
{
    /**
     * التأكد أن الوحدة (وكل ما تحتها) غير مرتبطة بأي قسم/مادة أخرى عبر أي آلية ربط/مزامنة
     * قديمة أو حديثة، قبل السماح بنقلها فعليًا لقسم آخر.
     *
     * @throws \InvalidArgumentException
     */
    public function assertMovable(Unit $unit): void
    {
        $ids = $this->collectSubtreeIds($unit);

        $syncedUnitsCount = Unit::query()
            ->whereIn('id', $ids['unit_ids'])
            ->where(function ($q) {
                $q->whereNotNull('sync_group_id')
                    ->orWhereNotNull('cloned_from_unit_id');
            })
            ->count();

        if ($syncedUnitsCount > 0) {
            throw new \InvalidArgumentException(
                'لا يمكن نقل هذه الوحدة لأنها (أو إحدى الوحدات الفرعية داخلها) مرتبطة حالياً بمواد/أقسام أخرى. '
                .'يرجى إلغاء الربط أولاً ثم إعادة المحاولة.'
            );
        }

        if (! empty($ids['lesson_ids'])) {
            $syncedLessonsCount = Lesson::query()
                ->whereIn('id', $ids['lesson_ids'])
                ->where(function ($q) {
                    $q->whereNotNull('sync_group_id')
                        ->orWhereNotNull('cloned_from_lesson_id');
                })
                ->count();

            if ($syncedLessonsCount > 0) {
                throw new \InvalidArgumentException(
                    'لا يمكن نقل هذه الوحدة لأن أحد دروسها مرتبط حالياً بمواد أخرى. '
                    .'يرجى إلغاء الربط أولاً ثم إعادة المحاولة.'
                );
            }
        }

        $mirroredElsewhereCount = DB::table('section_unit')
            ->whereIn('unit_id', $ids['unit_ids'])
            ->count();

        if ($mirroredElsewhereCount > 0) {
            throw new \InvalidArgumentException(
                'لا يمكن نقل هذه الوحدة لأنها تظهر حالياً كمرآة داخل قسم آخر. '
                .'يرجى إلغاء هذا الربط أولاً ثم إعادة المحاولة.'
            );
        }

        $legacyLessonUnitLinksCount = empty($ids['lesson_ids'])
            ? DB::table('lesson_units')->whereIn('unit_id', $ids['unit_ids'])->count()
            : DB::table('lesson_units')
                ->where(function ($q) use ($ids) {
                    $q->whereIn('lesson_id', $ids['lesson_ids'])
                        ->orWhereIn('unit_id', $ids['unit_ids']);
                })
                ->count();

        if ($legacyLessonUnitLinksCount > 0) {
            throw new \InvalidArgumentException(
                'لا يمكن نقل هذه الوحدة لأن أحد دروسها (أو الوحدة نفسها) مرتبط بوحدة أخرى (ربط قديم). '
                .'يرجى إلغاء هذا الربط أولاً ثم إعادة المحاولة.'
            );
        }

        $legacyQuizUnitLinksCount = empty($ids['quiz_ids'])
            ? DB::table('quiz_units')->whereIn('unit_id', $ids['unit_ids'])->count()
            : DB::table('quiz_units')
                ->where(function ($q) use ($ids) {
                    $q->whereIn('quiz_id', $ids['quiz_ids'])
                        ->orWhereIn('unit_id', $ids['unit_ids']);
                })
                ->count();

        if ($legacyQuizUnitLinksCount > 0) {
            throw new \InvalidArgumentException(
                'لا يمكن نقل هذه الوحدة لأن أحد اختباراتها (أو الوحدة نفسها) مرتبط بوحدة أخرى (ربط قديم). '
                .'يرجى إلغاء هذا الربط أولاً ثم إعادة المحاولة.'
            );
        }
    }

    /**
     * نقل الوحدة (وكل شجرتها: وحدات فرعية، دروس، اختبارات) فعلياً إلى قسم آخر (قد يكون في
     * مادة مختلفة)، إما كوحدة رئيسية فيه أو تحت وحدة أب محددة ضمنه، دون تكرار أو حذف أي صف.
     */
    public function moveUnitToSection(
        Unit $unit,
        SubjectSection $targetSection,
        ?int $targetParentUnitId = null
    ): Unit {
        if ((int) $unit->section_id === (int) $targetSection->id) {
            throw new \InvalidArgumentException(
                'الوحدة موجودة بالفعل في هذا القسم. لتغيير مكانها ضمن نفس القسم استخدم زر تعديل الوحدة.'
            );
        }

        if ($targetParentUnitId) {
            $targetParent = Unit::query()->find($targetParentUnitId);

            if (! $targetParent || (int) $targetParent->section_id !== (int) $targetSection->id) {
                throw new \InvalidArgumentException('الوحدة الأب يجب أن تنتمي للقسم الهدف.');
            }
        }

        return DB::transaction(function () use ($unit, $targetSection, $targetParentUnitId) {
            $ids = $this->collectSubtreeIds($unit);

            $newOrder = (Unit::query()
                ->where('section_id', $targetSection->id)
                ->where('parent_id', $targetParentUnitId)
                ->max('order') ?? 0) + 1;

            Unit::query()
                ->whereIn('id', $ids['unit_ids'])
                ->update(['section_id' => $targetSection->id]);

            Unit::query()
                ->where('id', $unit->id)
                ->update(['parent_id' => $targetParentUnitId, 'order' => $newOrder]);

            if (! empty($ids['lesson_ids'])) {
                Lesson::query()
                    ->whereIn('id', $ids['lesson_ids'])
                    ->update(['section_id' => $targetSection->id]);
            }

            if (! empty($ids['quiz_ids'])) {
                Quiz::query()
                    ->whereIn('id', $ids['quiz_ids'])
                    ->update([
                        'section_id' => $targetSection->id,
                        'subject_id' => $targetSection->subject_id,
                    ]);
            }

            return $unit->fresh();
        });
    }

    /**
     * جمع معرفات شجرة الوحدة: الوحدات الفرعية، الدروس، والاختبارات.
     *
     * @return array{unit_ids: array<int, int>, lesson_ids: array<int, int>, quiz_ids: array<int, int>}
     */
    protected function collectSubtreeIds(Unit $unit): array
    {
        $unitIds = $unit->collectSubtree()->pluck('id')->unique()->values()->all();

        $lessonIds = Lesson::query()
            ->whereIn('unit_id', $unitIds)
            ->pluck('id')
            ->unique()
            ->values()
            ->all();

        $quizIds = Quiz::query()
            ->where(function ($q) use ($unitIds, $lessonIds) {
                $q->whereIn('unit_id', $unitIds)
                    ->orWhereIn('lesson_id', $lessonIds);
            })
            ->pluck('id')
            ->unique()
            ->values()
            ->all();

        return [
            'unit_ids' => $unitIds,
            'lesson_ids' => $lessonIds,
            'quiz_ids' => $quizIds,
        ];
    }
}
