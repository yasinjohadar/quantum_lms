<?php

namespace App\Services\Curriculum;

use App\Models\Lesson;
use App\Models\Quiz;
use App\Models\Subject;
use App\Models\SubjectSection;
use App\Models\Unit;
use Illuminate\Support\Facades\DB;

class SectionMoveService
{
    /**
     * التأكد أن القسم (وكل ما تحته) غير مرتبط بأي مادة أخرى عبر أي آلية ربط/مزامنة
     * قديمة أو حديثة، قبل السماح بنقله فعليًا لمادة أخرى.
     *
     * @throws \InvalidArgumentException
     */
    public function assertMovable(SubjectSection $section): void
    {
        $ids = $this->collectSubtreeIds($section);

        $syncedSectionsCount = SubjectSection::query()
            ->whereIn('id', $ids['section_ids'])
            ->where(function ($q) {
                $q->whereNotNull('sync_group_id')
                    ->orWhereNotNull('cloned_from_section_id');
            })
            ->count();

        if ($syncedSectionsCount > 0) {
            throw new \InvalidArgumentException(
                'لا يمكن نقل هذا القسم لأنه (أو أحد الأقسام الفرعية داخله) مرتبط حالياً بمواد أخرى. '
                .'يرجى إلغاء الربط أولاً من مودال "ربط القسم بمواد إضافية" ثم إعادة المحاولة.'
            );
        }

        if (! empty($ids['unit_ids'])) {
            $syncedUnitsCount = Unit::query()
                ->whereIn('id', $ids['unit_ids'])
                ->where(function ($q) {
                    $q->whereNotNull('sync_group_id')
                        ->orWhereNotNull('cloned_from_unit_id');
                })
                ->count();

            if ($syncedUnitsCount > 0) {
                throw new \InvalidArgumentException(
                    'لا يمكن نقل هذا القسم لأن إحدى وحداته مرتبطة حالياً بمواد أخرى. '
                    .'يرجى إلغاء الربط أولاً ثم إعادة المحاولة.'
                );
            }
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
                    'لا يمكن نقل هذا القسم لأن أحد دروسه مرتبط حالياً بمواد أخرى. '
                    .'يرجى إلغاء الربط أولاً ثم إعادة المحاولة.'
                );
            }
        }

        $legacySectionLinksCount = DB::table('section_subjects')
            ->whereIn('section_id', $ids['section_ids'])
            ->count();

        if ($legacySectionLinksCount > 0) {
            throw new \InvalidArgumentException(
                'لا يمكن نقل هذا القسم لأنه مرتبط حالياً بمواد أخرى (ربط قديم). '
                .'يرجى إلغاء الربط أولاً ثم إعادة المحاولة.'
            );
        }

        $legacyLessonUnitLinksCount = empty($ids['lesson_ids']) && empty($ids['unit_ids'])
            ? 0
            : DB::table('lesson_units')
                ->where(function ($q) use ($ids) {
                    $q->whereIn('lesson_id', $ids['lesson_ids'])
                        ->orWhereIn('unit_id', $ids['unit_ids']);
                })
                ->count();

        if ($legacyLessonUnitLinksCount > 0) {
            throw new \InvalidArgumentException(
                'لا يمكن نقل هذا القسم لأن أحد دروسه أو وحداته مرتبط بوحدة أخرى (ربط قديم). '
                .'يرجى إلغاء هذا الربط أولاً ثم إعادة المحاولة.'
            );
        }

        $legacyQuizUnitLinksCount = empty($ids['quiz_ids']) && empty($ids['unit_ids'])
            ? 0
            : DB::table('quiz_units')
                ->where(function ($q) use ($ids) {
                    $q->whereIn('quiz_id', $ids['quiz_ids'])
                        ->orWhereIn('unit_id', $ids['unit_ids']);
                })
                ->count();

        if ($legacyQuizUnitLinksCount > 0) {
            throw new \InvalidArgumentException(
                'لا يمكن نقل هذا القسم لأن أحد اختباراته أو وحداته مرتبط بوحدة أخرى (ربط قديم). '
                .'يرجى إلغاء هذا الربط أولاً ثم إعادة المحاولة.'
            );
        }
    }

    /**
     * نقل القسم (وكل شجرته: أقسام فرعية، وحدات، دروس، اختبارات) فعلياً إلى مادة أخرى،
     * إما كقسم رئيسي فيها أو تحت قسم أب محدد ضمنها، دون تكرار أو حذف أي صف موجود.
     */
    public function moveSectionToSubject(
        SubjectSection $section,
        Subject $targetSubject,
        ?int $targetParentSectionId = null
    ): SubjectSection {
        if ((int) $section->subject_id === (int) $targetSubject->id) {
            throw new \InvalidArgumentException(
                'القسم موجود بالفعل في هذه المادة. لتغيير مكانه ضمن نفس المادة استخدم زر تعديل القسم.'
            );
        }

        if ($targetParentSectionId) {
            $targetParent = SubjectSection::query()->find($targetParentSectionId);

            if (! $targetParent || (int) $targetParent->subject_id !== (int) $targetSubject->id) {
                throw new \InvalidArgumentException('القسم الأب يجب أن ينتمي للمادة الهدف.');
            }
        }

        return DB::transaction(function () use ($section, $targetSubject, $targetParentSectionId) {
            $ids = $this->collectSubtreeIds($section);

            $newOrder = (SubjectSection::query()
                ->where('subject_id', $targetSubject->id)
                ->where('parent_id', $targetParentSectionId)
                ->max('order') ?? -1) + 1;

            SubjectSection::query()
                ->whereIn('id', $ids['section_ids'])
                ->update(['subject_id' => $targetSubject->id]);

            SubjectSection::query()
                ->where('id', $section->id)
                ->update(['parent_id' => $targetParentSectionId, 'order' => $newOrder]);

            if (! empty($ids['quiz_ids'])) {
                Quiz::query()
                    ->whereIn('id', $ids['quiz_ids'])
                    ->update(['subject_id' => $targetSubject->id]);
            }

            return $section->fresh();
        });
    }

    /**
     * جمع معرفات كل شجرة القسم: الأقسام الفرعية، الوحدات، الدروس، والاختبارات.
     *
     * @return array{section_ids: array<int, int>, unit_ids: array<int, int>, lesson_ids: array<int, int>, quiz_ids: array<int, int>}
     */
    protected function collectSubtreeIds(SubjectSection $section): array
    {
        $sectionIds = $section->collectSubtree()->pluck('id')->unique()->values()->all();

        $unitIds = Unit::query()
            ->whereIn('section_id', $sectionIds)
            ->pluck('id')
            ->unique()
            ->values()
            ->all();

        $lessonIds = Lesson::query()
            ->where(function ($q) use ($sectionIds, $unitIds) {
                $q->whereIn('section_id', $sectionIds)
                    ->orWhereIn('unit_id', $unitIds);
            })
            ->pluck('id')
            ->unique()
            ->values()
            ->all();

        $quizIds = Quiz::query()
            ->where(function ($q) use ($sectionIds, $unitIds, $lessonIds) {
                $q->whereIn('section_id', $sectionIds)
                    ->orWhereIn('unit_id', $unitIds)
                    ->orWhereIn('lesson_id', $lessonIds);
            })
            ->pluck('id')
            ->unique()
            ->values()
            ->all();

        return [
            'section_ids' => $sectionIds,
            'unit_ids' => $unitIds,
            'lesson_ids' => $lessonIds,
            'quiz_ids' => $quizIds,
        ];
    }
}
