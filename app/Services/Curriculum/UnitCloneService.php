<?php

namespace App\Services\Curriculum;

use App\Models\SubjectSection;
use App\Models\Unit;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class UnitCloneService
{
    use CurriculumTreeCloneTrait;

    public function cloneUnitTreeToSection(Unit $anchor, SubjectSection $targetSection): ?Unit
    {
        if ($anchor->section_id === $targetSection->id) {
            return null;
        }

        $existing = Unit::query()
            ->where('cloned_from_unit_id', $anchor->id)
            ->where('section_id', $targetSection->id)
            ->whereNull('parent_id')
            ->first();

        if ($existing) {
            return $existing;
        }

        return DB::transaction(function () use ($anchor, $targetSection) {
            $syncGroupId = $this->ensureSyncGroup($anchor);
            $targetSubject = $targetSection->subject ?? $targetSection->subject()->firstOrFail();

            return $this->cloneUnitSubtree($anchor, $targetSection, $targetSubject, $syncGroupId);
        });
    }

    public function removeMirrorForSection(Unit $anchor, SubjectSection $targetSection): void
    {
        $mirrorRoot = Unit::query()
            ->where('cloned_from_unit_id', $anchor->id)
            ->where('section_id', $targetSection->id)
            ->whereNull('parent_id')
            ->first();

        if (! $mirrorRoot) {
            return;
        }

        DB::transaction(function () use ($mirrorRoot) {
            $this->deleteMirrorSubtree($mirrorRoot);
        });
    }

    public function ensureSyncGroup(Unit $anchor): string
    {
        if ($anchor->sync_group_id) {
            return $anchor->sync_group_id;
        }

        $syncGroupId = (string) Str::uuid();
        $subtree = $this->collectUnitSubtree($anchor);

        Unit::query()
            ->whereIn('id', $subtree->pluck('id'))
            ->update([
                'sync_group_id' => $syncGroupId,
                'is_sync_canonical' => true,
            ]);

        $anchor->refresh();

        return $syncGroupId;
    }

    protected function cloneUnitSubtree(
        Unit $anchor,
        SubjectSection $targetSection,
        $targetSubject,
        string $syncGroupId
    ): Unit {
        $canonicalUnits = $this->collectUnitSubtree($anchor);
        $unitMap = [];
        $lessonMap = [];
        $quizMap = [];
        $sectionMap = [];

        $sorted = $canonicalUnits->sortBy(fn (Unit $u) => $this->unitDepth($u, $canonicalUnits));

        foreach ($sorted as $canonicalUnit) {
            $parentId = null;
            if ($canonicalUnit->parent_id && isset($unitMap[$canonicalUnit->parent_id])) {
                $parentId = $unitMap[$canonicalUnit->parent_id];
            }

            $mirrorUnit = Unit::create([
                'section_id' => $targetSection->id,
                'sync_group_id' => $syncGroupId,
                'is_sync_canonical' => false,
                'cloned_from_unit_id' => $canonicalUnit->id,
                'parent_id' => $parentId,
                'title' => $canonicalUnit->title,
                'description' => $canonicalUnit->description,
                'order' => $canonicalUnit->order,
                'is_active' => $canonicalUnit->is_active,
            ]);

            $unitMap[$canonicalUnit->id] = $mirrorUnit->id;

            $this->registerPeer(
                $syncGroupId,
                \App\Models\SectionSyncPeer::TYPE_UNIT,
                $canonicalUnit->id,
                $mirrorUnit->id,
                $targetSubject->id
            );
        }

        $sectionMap[$anchor->section_id] = $targetSection->id;

        foreach ($unitMap as $canonicalUnitId => $mirrorUnitId) {
            $this->cloneUnitLessons(
                $canonicalUnitId,
                $mirrorUnitId,
                $targetSection->id,
                $targetSubject,
                $syncGroupId,
                $lessonMap,
                $sectionMap,
                $unitMap
            );
            $this->cloneUnitQuizzes(
                $canonicalUnitId,
                $mirrorUnitId,
                $targetSubject,
                $syncGroupId,
                $quizMap,
                $sectionMap,
                $lessonMap
            );
        }

        return Unit::findOrFail($unitMap[$anchor->id]);
    }

    public function deleteMirrorSubtree(Unit $mirrorRoot): void
    {
        $subtree = $this->collectUnitSubtree($mirrorRoot);
        $unitIds = $subtree->pluck('id')->all();

        $this->deleteUnitTreeByIds($unitIds, true);
    }

    public function deleteCanonicalSubtree(Unit $anchor): void
    {
        $subtree = $this->collectUnitSubtree($anchor);
        $unitIds = $subtree->pluck('id')->all();

        $this->deleteUnitTreeByIds($unitIds, false);

        DB::table('section_unit')->whereIn('unit_id', $unitIds)->delete();
    }
}
