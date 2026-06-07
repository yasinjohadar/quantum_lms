<?php

namespace App\Services\Curriculum;

use App\Models\Lesson;
use App\Models\Unit;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class LessonCloneService
{
    use CurriculumTreeCloneTrait;

    public function cloneLessonToUnit(Lesson $anchor, Unit $targetUnit): ?Lesson
    {
        if ($anchor->isSyncMirror()) {
            throw new \InvalidArgumentException('لا يمكن ربط نسخة مرتبطة بوحدات أخرى.');
        }

        if ($anchor->unit_id && (int) $anchor->unit_id === (int) $targetUnit->id) {
            return null;
        }

        $existing = Lesson::query()
            ->where('cloned_from_lesson_id', $anchor->id)
            ->where('unit_id', $targetUnit->id)
            ->first();

        if ($existing) {
            return $existing;
        }

        return DB::transaction(function () use ($anchor, $targetUnit) {
            $syncGroupId = $this->ensureSyncGroup($anchor);
            $targetUnit->loadMissing('section.subject');
            $targetSubject = $targetUnit->section->subject ?? $targetUnit->section->subject()->firstOrFail();

            $lessonMap = [];
            $quizMap = [];

            $anchor->loadMissing(['attachments', 'quizzes']);

            $this->cloneLessonRecord(
                $anchor,
                $targetUnit->id,
                $targetUnit->section_id,
                $targetSubject,
                $syncGroupId,
                $lessonMap
            );

            $mirrorLessonId = $lessonMap[$anchor->id] ?? null;
            if (! $mirrorLessonId) {
                return null;
            }

            $this->cloneLessonQuizzes(
                $anchor->id,
                $mirrorLessonId,
                $targetUnit,
                $targetSubject,
                $syncGroupId,
                $quizMap
            );

            DB::table('lesson_units')
                ->where('lesson_id', $anchor->id)
                ->where('unit_id', $targetUnit->id)
                ->delete();

            return Lesson::find($mirrorLessonId);
        });
    }

    public function removeMirrorForUnit(Lesson $anchor, Unit $targetUnit): void
    {
        $mirror = Lesson::query()
            ->where('cloned_from_lesson_id', $anchor->id)
            ->where('unit_id', $targetUnit->id)
            ->first();

        if (! $mirror) {
            return;
        }

        DB::transaction(function () use ($mirror) {
            $this->deleteMirrorRecord($mirror);
        });
    }

    public function ensureSyncGroup(Lesson $anchor): string
    {
        if ($anchor->sync_group_id) {
            return $anchor->sync_group_id;
        }

        $syncGroupId = (string) Str::uuid();

        Lesson::query()
            ->where('id', $anchor->id)
            ->update([
                'sync_group_id' => $syncGroupId,
                'is_sync_canonical' => true,
            ]);

        $anchor->refresh();

        return $syncGroupId;
    }

    public function deleteMirrorRecord(Lesson $mirror): void
    {
        if (! $mirror->isSyncMirror()) {
            return;
        }

        $this->deleteLessonByIds([$mirror->id], true);
    }

    public function deleteCanonicalRecord(Lesson $anchor): void
    {
        if ($anchor->isSyncMirror()) {
            return;
        }

        $this->deleteLessonByIds([$anchor->id], false);
    }
}
