<?php

namespace App\Services\Curriculum;

use App\Models\Lesson;
use App\Models\LessonAttachment;
use App\Models\Quiz;
use App\Models\QuizQuestion;
use App\Models\SectionSyncPeer;
use App\Models\Subject;
use App\Models\SubjectSection;
use App\Models\Unit;
use Illuminate\Support\Collection;

trait CurriculumTreeCloneTrait
{
    protected function registerPeer(
        string $syncGroupId,
        string $entityType,
        int $canonicalEntityId,
        int $peerEntityId,
        int $targetSubjectId
    ): void {
        SectionSyncPeer::query()->firstOrCreate(
            [
                'entity_type' => $entityType,
                'canonical_entity_id' => $canonicalEntityId,
                'peer_entity_id' => $peerEntityId,
            ],
            [
                'sync_group_id' => $syncGroupId,
                'target_subject_id' => $targetSubjectId,
            ]
        );
    }

    /**
     * @param  Collection<int, Unit>  $units
     * @param  array<int, int>  $unitMap
     */
    protected function cloneUnitsForSection(
        Collection $units,
        int $mirrorSectionId,
        Subject $targetSubject,
        string $syncGroupId,
        array &$unitMap,
        bool $withUnitSyncFields = false
    ): void {
        $sorted = $units->sortBy(fn (Unit $u) => $this->unitDepth($u, $units));

        foreach ($sorted as $canonicalUnit) {
            $parentId = null;
            if ($canonicalUnit->parent_id && isset($unitMap[$canonicalUnit->parent_id])) {
                $parentId = $unitMap[$canonicalUnit->parent_id];
            }

            $attributes = [
                'section_id' => $mirrorSectionId,
                'parent_id' => $parentId,
                'title' => $canonicalUnit->title,
                'description' => $canonicalUnit->description,
                'order' => $canonicalUnit->order,
                'is_active' => $canonicalUnit->is_active,
            ];

            if ($withUnitSyncFields) {
                $attributes['sync_group_id'] = $syncGroupId;
                $attributes['is_sync_canonical'] = false;
                $attributes['cloned_from_unit_id'] = $canonicalUnit->id;
            }

            $mirrorUnit = Unit::create($attributes);
            $unitMap[$canonicalUnit->id] = $mirrorUnit->id;

            $this->registerPeer(
                $syncGroupId,
                SectionSyncPeer::TYPE_UNIT,
                $canonicalUnit->id,
                $mirrorUnit->id,
                $targetSubject->id
            );
        }
    }

    /**
     * @param  array<int, int>  $sectionMap
     * @param  array<int, int>  $unitMap
     */
    protected function cloneMirroredUnitsPivot(
        int $canonicalSectionId,
        int $mirrorSectionId,
        array $sectionMap,
        array $unitMap
    ): void {
        $section = SubjectSection::with('mirroredUnits')->find($canonicalSectionId);
        if (! $section) {
            return;
        }

        $syncData = [];
        foreach ($section->mirroredUnits as $unit) {
            if (! isset($unitMap[$unit->id])) {
                continue;
            }
            $syncData[$unitMap[$unit->id]] = ['order' => (int) ($unit->pivot->order ?? 0)];
        }

        if ($syncData !== []) {
            SubjectSection::find($mirrorSectionId)?->mirroredUnits()->sync($syncData);
        }
    }

    /**
     * @param  array<int, int>  $lessonMap
     */
    protected function cloneDirectLessons(
        int $canonicalSectionId,
        int $mirrorSectionId,
        Subject $targetSubject,
        string $syncGroupId,
        array &$lessonMap
    ): void {
        $lessons = Lesson::query()
            ->where('section_id', $canonicalSectionId)
            ->whereNull('unit_id')
            ->orderBy('order')
            ->get();

        foreach ($lessons as $canonicalLesson) {
            $this->cloneLessonRecord(
                $canonicalLesson,
                null,
                $mirrorSectionId,
                $targetSubject,
                $syncGroupId,
                $lessonMap
            );
        }
    }

    /**
     * @param  array<int, int>  $lessonMap
     * @param  array<int, int>  $sectionMap
     * @param  array<int, int>  $unitMap
     */
    protected function cloneUnitLessons(
        int $canonicalUnitId,
        int $mirrorUnitId,
        ?int $fallbackMirrorSectionId,
        Subject $targetSubject,
        string $syncGroupId,
        array &$lessonMap,
        array $sectionMap,
        array $unitMap = []
    ): void {
        $lessons = Lesson::query()
            ->where('unit_id', $canonicalUnitId)
            ->orderBy('order')
            ->with(['attachments', 'linkedUnits'])
            ->get();

        $mirrorSectionId = Unit::find($mirrorUnitId)?->section_id ?? $fallbackMirrorSectionId;

        foreach ($lessons as $canonicalLesson) {
            $mirrorSectionForLesson = $canonicalLesson->section_id && isset($sectionMap[$canonicalLesson->section_id])
                ? $sectionMap[$canonicalLesson->section_id]
                : $mirrorSectionId;

            $this->cloneLessonRecord(
                $canonicalLesson,
                $mirrorUnitId,
                $mirrorSectionForLesson,
                $targetSubject,
                $syncGroupId,
                $lessonMap,
                $unitMap
            );
        }
    }

    /**
     * @param  array<int, int>  $lessonMap
     * @param  array<int, int>  $unitMap
     */
    protected function cloneLessonRecord(
        Lesson $canonicalLesson,
        ?int $mirrorUnitId,
        ?int $mirrorSectionId,
        Subject $targetSubject,
        string $syncGroupId,
        array &$lessonMap,
        array $unitMap = []
    ): void {
        if (isset($lessonMap[$canonicalLesson->id])) {
            return;
        }

        $mirrorLesson = Lesson::create([
            'unit_id' => $mirrorUnitId,
            'section_id' => $mirrorSectionId,
            'sync_group_id' => $syncGroupId,
            'is_sync_canonical' => false,
            'cloned_from_lesson_id' => $canonicalLesson->id,
            'title' => $canonicalLesson->title,
            'description' => $canonicalLesson->description,
            'video_type' => $canonicalLesson->video_type,
            'video_url' => $canonicalLesson->video_url,
            'video_id' => $canonicalLesson->video_id,
            'thumbnail' => $canonicalLesson->thumbnail,
            'duration' => $canonicalLesson->duration,
            'book_page_from' => $canonicalLesson->book_page_from,
            'book_page_to' => $canonicalLesson->book_page_to,
            'order' => $canonicalLesson->order,
            'is_active' => $canonicalLesson->is_active,
            'is_free' => $canonicalLesson->is_free,
            'is_preview' => $canonicalLesson->is_preview,
            'review_status' => $canonicalLesson->review_status,
        ]);

        $lessonMap[$canonicalLesson->id] = $mirrorLesson->id;

        $this->registerPeer(
            $syncGroupId,
            SectionSyncPeer::TYPE_LESSON,
            $canonicalLesson->id,
            $mirrorLesson->id,
            $targetSubject->id
        );

        foreach ($canonicalLesson->attachments as $attachment) {
            LessonAttachment::create([
                'lesson_id' => $mirrorLesson->id,
                'type' => $attachment->type,
                'title' => $attachment->title,
                'description' => $attachment->description,
                'file_path' => $attachment->file_path,
                'file_name' => $attachment->file_name,
                'file_type' => $attachment->file_type,
                'file_size' => $attachment->file_size,
                'url' => $attachment->url,
                'order' => $attachment->order,
                'is_downloadable' => $attachment->is_downloadable,
                'is_active' => $attachment->is_active,
            ]);
        }

    }

    /**
     * @param  array<int, int>  $quizMap
     */
    protected function cloneLessonQuizzes(
        int $canonicalLessonId,
        int $mirrorLessonId,
        Unit $mirrorUnit,
        Subject $targetSubject,
        string $syncGroupId,
        array &$quizMap
    ): void {
        $quizzes = Quiz::query()
            ->where('lesson_id', $canonicalLessonId)
            ->orderBy('order')
            ->get();

        foreach ($quizzes as $canonicalQuiz) {
            $this->cloneQuizRecord(
                $canonicalQuiz,
                $targetSubject->id,
                $mirrorUnit->section_id,
                $mirrorUnit->id,
                $mirrorLessonId,
                $syncGroupId,
                $quizMap
            );
        }
    }

    /**
     * @param  array<int, int>  $quizMap
     */
    protected function cloneSectionQuizzes(
        int $canonicalSectionId,
        int $mirrorSectionId,
        Subject $targetSubject,
        string $syncGroupId,
        array &$quizMap
    ): void {
        $quizzes = Quiz::query()
            ->where('section_id', $canonicalSectionId)
            ->whereNull('unit_id')
            ->orderBy('order')
            ->get();

        foreach ($quizzes as $canonicalQuiz) {
            $this->cloneQuizRecord(
                $canonicalQuiz,
                $targetSubject->id,
                $mirrorSectionId,
                null,
                null,
                $syncGroupId,
                $quizMap
            );
        }
    }

    /**
     * @param  array<int, int>  $quizMap
     * @param  array<int, int>  $sectionMap
     */
    protected function cloneUnitQuizzes(
        int $canonicalUnitId,
        int $mirrorUnitId,
        Subject $targetSubject,
        string $syncGroupId,
        array &$quizMap,
        array $sectionMap,
        array $lessonMap = []
    ): void {
        $quizzes = Quiz::query()
            ->where('unit_id', $canonicalUnitId)
            ->orderBy('order')
            ->get();

        $mirrorSectionId = Unit::find($mirrorUnitId)?->section_id;

        foreach ($quizzes as $canonicalQuiz) {
            $mirrorSectionForQuiz = $canonicalQuiz->section_id && isset($sectionMap[$canonicalQuiz->section_id])
                ? $sectionMap[$canonicalQuiz->section_id]
                : $mirrorSectionId;

            $mirrorLessonId = null;
            if ($canonicalQuiz->lesson_id) {
                $mirrorLessonId = $lessonMap[$canonicalQuiz->lesson_id]
                    ?? SectionSyncPeer::query()
                        ->where('entity_type', SectionSyncPeer::TYPE_LESSON)
                        ->where('canonical_entity_id', $canonicalQuiz->lesson_id)
                        ->where('target_subject_id', $targetSubject->id)
                        ->value('peer_entity_id');
            }

            $this->cloneQuizRecord(
                $canonicalQuiz,
                $targetSubject->id,
                $mirrorSectionForQuiz,
                $mirrorUnitId,
                $mirrorLessonId,
                $syncGroupId,
                $quizMap
            );
        }
    }

    /**
     * @param  array<int, int>  $quizMap
     */
    protected function cloneQuizRecord(
        Quiz $canonicalQuiz,
        int $targetSubjectId,
        ?int $mirrorSectionId,
        ?int $mirrorUnitId,
        ?int $mirrorLessonId,
        string $syncGroupId,
        array &$quizMap
    ): void {
        if (isset($quizMap[$canonicalQuiz->id])) {
            return;
        }

        $attributes = $canonicalQuiz->only($canonicalQuiz->getFillable());
        unset($attributes['created_by'], $attributes['reviewed_by']);
        $attributes['subject_id'] = $targetSubjectId;
        $attributes['section_id'] = $mirrorSectionId;
        $attributes['unit_id'] = $mirrorUnitId;
        $attributes['lesson_id'] = $mirrorLessonId;

        $canonicalQuiz->loadMissing('quizQuestions');

        $mirrorQuiz = Quiz::create($attributes);
        $quizMap[$canonicalQuiz->id] = $mirrorQuiz->id;

        $this->registerPeer(
            $syncGroupId,
            SectionSyncPeer::TYPE_QUIZ,
            $canonicalQuiz->id,
            $mirrorQuiz->id,
            $targetSubjectId
        );

        foreach ($canonicalQuiz->quizQuestions as $quizQuestion) {
            QuizQuestion::create([
                'quiz_id' => $mirrorQuiz->id,
                'question_id' => $quizQuestion->question_id,
                'order' => $quizQuestion->order,
                'points' => $quizQuestion->points,
                'is_required' => $quizQuestion->is_required,
                'shuffle_options' => $quizQuestion->shuffle_options,
            ]);
        }
    }

    /**
     * @param  Collection<int, Unit>  $siblings
     */
    protected function unitDepth(Unit $unit, Collection $siblings): int
    {
        $depth = 0;
        $current = $unit;
        while ($current->parent_id) {
            $depth++;
            $current = $siblings->firstWhere('id', $current->parent_id) ?? $current;
            if (! $current->parent_id) {
                break;
            }
        }

        return $depth;
    }

    protected function collectUnitSubtree(Unit $anchor): Collection
    {
        $anchor->loadMissing(['children.children.children']);

        return $anchor->collectSubtree();
    }

    protected function deleteLessonByIds(array $lessonIds, bool $removePeers): void
    {
        if ($lessonIds === []) {
            return;
        }

        SectionSyncService::$syncing = true;

        try {
            $quizIds = Quiz::withTrashed()
                ->whereIn('lesson_id', $lessonIds)
                ->pluck('id')
                ->all();

            Quiz::whereIn('id', $quizIds)->delete();
            Lesson::whereIn('id', $lessonIds)->delete();

            if ($removePeers) {
                SectionSyncPeer::query()
                    ->whereIn('peer_entity_id', array_merge($lessonIds, $quizIds))
                    ->delete();
            }
        } finally {
            SectionSyncService::$syncing = false;
        }
    }

    protected function deleteUnitTreeByIds(array $unitIds, bool $removePeers): void
    {
        if ($unitIds === []) {
            return;
        }

        SectionSyncService::$syncing = true;

        try {
            $lessonIds = Lesson::withTrashed()
                ->whereIn('unit_id', $unitIds)
                ->pluck('id')
                ->all();

            $quizIds = Quiz::withTrashed()
                ->where(function ($q) use ($unitIds, $lessonIds) {
                    $q->whereIn('unit_id', $unitIds)
                        ->orWhereIn('lesson_id', $lessonIds);
                })
                ->pluck('id')
                ->all();

            Quiz::whereIn('id', $quizIds)->delete();
            Lesson::whereIn('id', $lessonIds)->delete();
            Unit::whereIn('id', $unitIds)->delete();

            if ($removePeers) {
                SectionSyncPeer::query()
                    ->whereIn('peer_entity_id', array_merge($unitIds, $lessonIds, $quizIds))
                    ->delete();
            }
        } finally {
            SectionSyncService::$syncing = false;
        }
    }
}
