<?php

namespace App\Http\Controllers\Admin\Concerns;

use App\Models\Subject;
use Illuminate\Database\Eloquent\Builder;

trait ProvidesLinkableCurriculum
{
    protected function linkableSubjectsQuery($user): Builder
    {
        $query = Subject::query()->with('schoolClass.stage');

        if ($user->usesTeacherAssignmentScope()) {
            $classIds = $user->assignedClasses()->pluck('classes.id');
            $subjectIds = $user->assignedSubjects()->pluck('subjects.id');
            $query->where(function ($q) use ($classIds, $subjectIds) {
                if ($classIds->isNotEmpty()) {
                    $q->whereIn('class_id', $classIds);
                }
                if ($subjectIds->isNotEmpty()) {
                    $q->orWhereIn('id', $subjectIds);
                }
            });
        } elseif ($user->usesSupervisorAssignmentScope()) {
            $classIds = $user->assignedClassesAsSupervisor()->pluck('classes.id');
            $subjectIds = $user->assignedSubjectsAsSupervisor()->pluck('subjects.id');
            $query->where(function ($q) use ($classIds, $subjectIds) {
                if ($classIds->isNotEmpty()) {
                    $q->whereIn('class_id', $classIds);
                }
                if ($subjectIds->isNotEmpty()) {
                    $q->orWhereIn('id', $subjectIds);
                }
                if ($classIds->isEmpty() && $subjectIds->isEmpty()) {
                    $q->whereRaw('1 = 0');
                }
            });
        }

        return $query->ordered();
    }

    /**
     * @return array{linkableStructure: \Illuminate\Support\Collection, linkableClasses: \Illuminate\Support\Collection}
     */
    protected function buildLinkableCurriculumPayload($user): array
    {
        $linkableSubjects = $this->linkableSubjectsQuery($user)->with([
            'sections' => fn ($q) => $q->orderBy('order')->orderBy('title'),
            'sections.units' => fn ($q) => $q->orderBy('order')->orderBy('title'),
        ])->get();

        $linkableStructure = $linkableSubjects->map(function ($s) {
            return [
                'id' => $s->id,
                'class_id' => $s->class_id ?? null,
                'name' => $s->name,
                'class_name' => $s->schoolClass->name ?? '',
                'stage_name' => $s->schoolClass->stage->name ?? '',
                'sections' => $s->sections->map(fn ($sec) => [
                    'id' => $sec->id,
                    'title' => $sec->title,
                    'path_title' => $sec->path_title,
                    'units' => $sec->units->map(fn ($u) => ['id' => $u->id, 'title' => $u->title])->values(),
                ])->values(),
            ];
        })->values();

        $linkableClasses = $linkableSubjects->pluck('schoolClass')->filter()->unique('id')->values()->map(fn ($c) => [
            'id' => $c->id,
            'name' => $c->name,
            'stage_name' => $c->stage->name ?? '',
        ])->values();

        return compact('linkableStructure', 'linkableClasses');
    }
}
