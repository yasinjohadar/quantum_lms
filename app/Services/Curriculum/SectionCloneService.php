<?php

namespace App\Services\Curriculum;

use App\Models\Lesson;
use App\Models\Quiz;
use App\Models\SectionSyncPeer;
use App\Models\Subject;
use App\Models\SubjectSection;
use App\Models\Unit;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class SectionCloneService
{
    use CurriculumTreeCloneTrait;

    /**
     * @return array<int, int> canonical section id => mirror section id
     */
    public function linkedSubjectIdsForSection(SubjectSection $anchor): array
    {
        return $anchor->linkedSubjectIdsViaSync();
    }

    public function cloneSectionTreeToSubject(
        SubjectSection $anchor,
        Subject $targetSubject,
        ?int $targetParentSectionId = null
    ): ?SubjectSection {
        if ($anchor->subject_id === $targetSubject->id) {
            return null;
        }

        $targetParentSectionId = $this->resolveTargetParentSectionId($anchor, $targetSubject, $targetParentSectionId);

        $existing = SubjectSection::query()
            ->where('cloned_from_section_id', $anchor->id)
            ->where('subject_id', $targetSubject->id)
            ->first();

        if ($existing) {
            return $existing;
        }

        return DB::transaction(function () use ($anchor, $targetSubject, $targetParentSectionId) {
            $syncGroupId = $this->ensureSyncGroup($anchor);

            return $this->cloneSubtree($anchor, $targetSubject, $syncGroupId, $targetParentSectionId);
        });
    }

    public function resolveTargetParentSectionId(
        SubjectSection $anchor,
        Subject $targetSubject,
        ?int $targetParentSectionId
    ): ?int {
        if (! $targetParentSectionId) {
            return null;
        }

        $parent = SubjectSection::query()->find($targetParentSectionId);

        if (! $parent || (int) $parent->subject_id !== (int) $targetSubject->id) {
            throw new \InvalidArgumentException('القسم الأب يجب أن ينتمي للمادة الهدف.');
        }

        if ((int) $parent->cloned_from_section_id === (int) $anchor->id) {
            throw new \InvalidArgumentException('لا يمكن جعل النسخة أباً لنفسها.');
        }

        return (int) $parent->id;
    }

    public function removeMirrorForSubject(SubjectSection $anchor, Subject $targetSubject): void
    {
        $mirrorRoot = SubjectSection::query()
            ->where('cloned_from_section_id', $anchor->id)
            ->where('subject_id', $targetSubject->id)
            ->first();

        if (! $mirrorRoot) {
            return;
        }

        DB::transaction(function () use ($mirrorRoot) {
            $this->deleteMirrorSubtree($mirrorRoot);
        });
    }

    public function ensureSyncGroup(SubjectSection $anchor): string
    {
        if ($anchor->sync_group_id) {
            return $anchor->sync_group_id;
        }

        $syncGroupId = (string) Str::uuid();
        $subtree = $this->loadSubtree($anchor);

        SubjectSection::query()
            ->whereIn('id', $subtree->pluck('id'))
            ->update([
                'sync_group_id' => $syncGroupId,
                'is_sync_canonical' => true,
            ]);

        $anchor->refresh();

        return $syncGroupId;
    }

    protected function cloneSubtree(
        SubjectSection $anchor,
        Subject $targetSubject,
        string $syncGroupId,
        ?int $targetParentSectionId = null
    ): SubjectSection {
        $canonicalSections = $this->loadSubtree($anchor);
        $sectionMap = [];
        $unitMap = [];
        $lessonMap = [];
        $quizMap = [];

        $sortedSections = $canonicalSections->sortBy(function (SubjectSection $section) use ($anchor) {
            return $this->sectionDepth($section, $anchor);
        });

        foreach ($sortedSections as $canonicalSection) {
            $parentId = null;
            if ($canonicalSection->parent_id && isset($sectionMap[$canonicalSection->parent_id])) {
                $parentId = $sectionMap[$canonicalSection->parent_id];
            } elseif ((int) $canonicalSection->id === (int) $anchor->id && $targetParentSectionId) {
                $parentId = $targetParentSectionId;
            }

            $mirrorSection = SubjectSection::create([
                'subject_id' => $targetSubject->id,
                'sync_group_id' => $syncGroupId,
                'is_sync_canonical' => false,
                'cloned_from_section_id' => $canonicalSection->id,
                'parent_id' => $parentId,
                'title' => $canonicalSection->title,
                'description' => $canonicalSection->description,
                'type' => $canonicalSection->type,
                'order' => $canonicalSection->order,
                'is_active' => $canonicalSection->is_active,
            ]);

            $sectionMap[$canonicalSection->id] = $mirrorSection->id;

            $this->registerPeer(
                $syncGroupId,
                SectionSyncPeer::TYPE_SECTION,
                $canonicalSection->id,
                $mirrorSection->id,
                $targetSubject->id
            );
        }

        $canonicalSectionIds = $canonicalSections->pluck('id')->all();

        $canonicalUnits = Unit::query()
            ->whereIn('section_id', $canonicalSectionIds)
            ->orderBy('section_id')
            ->orderBy('order')
            ->get();

        $unitsBySection = $canonicalUnits->groupBy('section_id');

        foreach ($unitsBySection as $canonicalSectionId => $units) {
            $this->cloneUnitsForSection(
                $units,
                $sectionMap[$canonicalSectionId],
                $targetSubject,
                $syncGroupId,
                $unitMap
            );
        }

        foreach ($canonicalSectionIds as $canonicalSectionId) {
            $mirrorSectionId = $sectionMap[$canonicalSectionId];

            $this->cloneMirroredUnitsPivot($canonicalSectionId, $mirrorSectionId, $sectionMap, $unitMap);
            $this->cloneDirectLessons($canonicalSectionId, $mirrorSectionId, $targetSubject, $syncGroupId, $lessonMap);
            $this->cloneSectionQuizzes($canonicalSectionId, $mirrorSectionId, $targetSubject, $syncGroupId, $quizMap);
        }

        foreach ($unitMap as $canonicalUnitId => $mirrorUnitId) {
            $this->cloneUnitLessons($canonicalUnitId, $mirrorUnitId, null, $targetSubject, $syncGroupId, $lessonMap, $sectionMap, $unitMap);
            $this->cloneUnitQuizzes($canonicalUnitId, $mirrorUnitId, $targetSubject, $syncGroupId, $quizMap, $sectionMap, $lessonMap);
        }

        return SubjectSection::findOrFail($sectionMap[$anchor->id]);
    }

    public function deleteMirrorSubtree(SubjectSection $mirrorRoot): void
    {
        SectionSyncService::$syncing = true;

        try {
            $subtree = $this->loadSubtree($mirrorRoot);
            $sectionIds = $subtree->pluck('id')->all();

            $unitIds = Unit::withTrashed()
                ->whereIn('section_id', $sectionIds)
                ->pluck('id')
                ->all();

            $lessonIds = Lesson::withTrashed()
                ->where(function ($q) use ($sectionIds, $unitIds) {
                    $q->whereIn('section_id', $sectionIds)
                        ->orWhereIn('unit_id', $unitIds);
                })
                ->pluck('id')
                ->all();

            $quizIds = Quiz::withTrashed()
                ->where(function ($q) use ($sectionIds, $unitIds, $lessonIds) {
                    $q->whereIn('section_id', $sectionIds)
                        ->orWhereIn('unit_id', $unitIds)
                        ->orWhereIn('lesson_id', $lessonIds);
                })
                ->pluck('id')
                ->all();

            Quiz::whereIn('id', $quizIds)->delete();
            Lesson::whereIn('id', $lessonIds)->delete();
            Unit::whereIn('id', $unitIds)->delete();
            SubjectSection::whereIn('id', $sectionIds)->delete();

            SectionSyncPeer::query()
                ->whereIn('peer_entity_id', array_merge($sectionIds, $unitIds, $lessonIds, $quizIds))
                ->delete();
        } finally {
            SectionSyncService::$syncing = false;
        }
    }

    public function deleteCanonicalSubtree(SubjectSection $anchor): void
    {
        SectionSyncService::$syncing = true;

        try {
            $subtree = $this->loadSubtree($anchor);
            $sectionIds = $subtree->pluck('id')->all();

            $unitIds = Unit::query()
                ->whereIn('section_id', $sectionIds)
                ->pluck('id')
                ->all();

            $lessonIds = Lesson::query()
                ->where(function ($q) use ($sectionIds, $unitIds) {
                    $q->whereIn('section_id', $sectionIds)
                        ->orWhereIn('unit_id', $unitIds);
                })
                ->pluck('id')
                ->all();

            $quizIds = Quiz::query()
                ->where(function ($q) use ($sectionIds, $unitIds, $lessonIds) {
                    $q->whereIn('section_id', $sectionIds)
                        ->orWhereIn('unit_id', $unitIds)
                        ->orWhereIn('lesson_id', $lessonIds);
                })
                ->pluck('id')
                ->all();

            Quiz::whereIn('id', $quizIds)->delete();
            Lesson::whereIn('id', $lessonIds)->delete();
            Unit::whereIn('id', $unitIds)->delete();
            SubjectSection::whereIn('id', $sectionIds)->delete();

            DB::table('section_subjects')->whereIn('section_id', $sectionIds)->delete();
        } finally {
            SectionSyncService::$syncing = false;
        }
    }

    protected function loadSubtree(SubjectSection $anchor): Collection
    {
        $anchor->loadMissing(['children.children.children']);

        return $anchor->collectSubtree();
    }

    protected function sectionDepth(SubjectSection $section, SubjectSection $root): int
    {
        $depth = 0;
        $current = $section;
        while ($current->parent_id && $current->id !== $root->id) {
            $depth++;
            $current = SubjectSection::find($current->parent_id) ?? $current;
            if ($current->id === $root->id) {
                break;
            }
        }

        return $depth;
    }
}
