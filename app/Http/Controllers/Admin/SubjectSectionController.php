<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\StoreSubjectSectionRequest;
use App\Http\Requests\Admin\UpdateSubjectSectionRequest;
use App\Models\Subject;
use App\Models\SubjectSection;
use App\Services\Curriculum\SectionCloneService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class SubjectSectionController extends Controller
{
    public function __construct(
        protected SectionCloneService $cloneService
    ) {
        $this->middleware(['permission:subject-section-create'])->only('store');
        $this->middleware(['permission:subject-section-edit'])->only(['update', 'reorder', 'getLinkedSubjects', 'linkSubjects']);
        $this->middleware(['permission:subject-section-delete'])->only('destroy');
    }

    /**
     * تخزين قسم جديد تابع لمادة معيّنة.
     */
    public function store(StoreSubjectSectionRequest $request, Subject $subject)
    {
        Log::info('محاولة إنشاء قسم جديد للمادة: ' . $subject->id, $request->all());

        try {
            $data = $request->validated();
            $data['subject_id'] = $subject->id;
            $data['is_active'] = $request->has('is_active');

            $parentId = $request->input('parent_id');
            if ($parentId) {
                $parent = SubjectSection::find($parentId);
                if (!$parent || $parent->subject_id != $subject->id) {
                    return redirect()
                        ->route('admin.subjects.show', $subject->id)
                        ->with('error', 'القسم الأب يجب أن ينتمي لنفس المادة.');
                }
                $data['parent_id'] = $parentId;
            } else {
                $data['parent_id'] = null;
            }

            // لو لم يُرسل ترتيب نضعه في آخر القائمة (بين الأخوة فقط)، بأرقام 0، 1، 2... لعرض 1، 2، 3
            if (!isset($data['order']) || $data['order'] === null) {
                $maxOrder = SubjectSection::where('subject_id', $subject->id)
                    ->where('parent_id', $data['parent_id'])
                    ->max('order');
                $data['order'] = ($maxOrder ?? -1) + 1;
            }

            Log::info('البيانات المجهزة للحفظ:', $data);

            $section = SubjectSection::create($data);

            Log::info('تم إنشاء القسم بنجاح، ID: ' . $section->id);

            return redirect()
                ->route('admin.subjects.show', $subject->id)
                ->with('success', 'تم إنشاء قسم جديد للمادة بنجاح.');
        } catch (\Exception $e) {
            Log::error('خطأ في إنشاء قسم: ' . $e->getMessage(), [
                'subject_id' => $subject->id,
                'trace' => $e->getTraceAsString()
            ]);

            return redirect()
                ->route('admin.subjects.show', $subject->id)
                ->with('error', 'حدث خطأ أثناء إنشاء القسم: ' . $e->getMessage());
        }
    }

    /**
     * تحديث قسم موجود.
     */
    public function update(UpdateSubjectSectionRequest $request, SubjectSection $section)
    {
        try {
            $data = $request->validated();
            $data['is_active'] = $request->has('is_active');

            $parentId = $request->input('parent_id');
            if ($parentId !== null && $parentId !== '') {
                $parentId = (int) $parentId;
                if ($parentId === $section->id) {
                    return redirect()
                        ->route('admin.subjects.show', $section->subject_id)
                        ->with('error', 'لا يمكن جعل القسم أباً لنفسه.');
                }
                $parent = SubjectSection::with('children')->find($parentId);
                if (!$parent || $parent->subject_id != $section->subject_id) {
                    return redirect()
                        ->route('admin.subjects.show', $section->subject_id)
                        ->with('error', 'القسم الأب يجب أن ينتمي لنفس المادة.');
                }
                $descendantIds = $section->load('children')->getDescendantIds();
                if ($descendantIds->contains($parentId)) {
                    return redirect()
                        ->route('admin.subjects.show', $section->subject_id)
                        ->with('error', 'لا يمكن جعل القسم أباً لأحد أحفاده (منع الحلقات).');
                }
                $data['parent_id'] = $parentId;
            } else {
                $data['parent_id'] = null;
            }

            $section->update($data);

            return redirect()
                ->route('admin.subjects.show', $section->subject_id)
                ->with('success', 'تم تحديث بيانات القسم بنجاح.');
        } catch (\Exception $e) {
            return redirect()
                ->route('admin.subjects.show', $section->subject_id)
                ->with('error', 'حدث خطأ أثناء تحديث القسم: ' . $e->getMessage());
        }
    }

    /**
     * إعادة ترتيب الأقسام (جذر أو أبناء قسم معيّن).
     */
    public function reorder(Request $request, Subject $subject)
    {
        $request->validate([
            'order' => ['required', 'array'],
            'order.*' => ['integer', 'exists:subject_sections,id'],
            'parent_id' => ['nullable', 'integer', 'exists:subject_sections,id'],
        ]);

        $order = $request->input('order');
        $parentId = $request->input('parent_id') ?: null;

        $sections = SubjectSection::where('subject_id', $subject->id)
            ->where('parent_id', $parentId)
            ->whereIn('id', $order)
            ->get();

        if ($sections->count() !== count($order)) {
            return response()->json(['success' => false, 'message' => 'بعض الأقسام لا تنتمي لهذا السياق.'], 422);
        }

        foreach ($order as $index => $sectionId) {
            SubjectSection::where('id', $sectionId)->update(['order' => $index]);
        }

        return response()->json(['success' => true]);
    }

    /**
     * إرجاع المواد المرتبطة بالقسم (للمودال).
     */
    public function getLinkedSubjects(SubjectSection $section)
    {
        $syncSubjectIds = $section->linkedSubjectIdsViaSync();
        $legacySubjectIds = DB::table('section_subjects')
            ->where('section_id', $section->id)
            ->pluck('subject_id')
            ->all();

        $linkedSubjectIds = collect($syncSubjectIds)
            ->merge($legacySubjectIds)
            ->unique()
            ->values();

        $subjects = Subject::with('schoolClass.stage')
            ->whereIn('id', $linkedSubjectIds)
            ->get();

        $data = $subjects->map(function ($s) use ($section) {
            $mirrorRoot = SubjectSection::query()
                ->where('cloned_from_section_id', $section->id)
                ->where('subject_id', $s->id)
                ->first();

            $parentSection = $mirrorRoot?->parent_id
                ? SubjectSection::find($mirrorRoot->parent_id)
                : null;

            return [
                'id' => $s->id,
                'name' => $s->name ?? '',
                'class_name' => optional($s->schoolClass)->name ?? '',
                'stage_name' => optional(optional($s->schoolClass)->stage)->name ?? '',
                'label' => $this->formatLinkedSubjectBadgeText($s),
                'parent_section_id' => $mirrorRoot?->parent_id,
                'parent_section_label' => $parentSection
                    ? ($parentSection->path_title ?? $parentSection->title)
                    : null,
            ];
        })->values();

        return response()->json($data);
    }

    /**
     * نص موحّد لعرض مادة مرتبطة (مرحلة / صف — اسم المادة) مثل وحدات المرآة.
     */
    private function formatLinkedSubjectBadgeText(Subject $s): string
    {
        $stage = (string) (data_get($s, 'schoolClass.stage.name') ?? '');
        $class = (string) (data_get($s, 'schoolClass.name') ?? '');
        $name = (string) ($s->name ?? '');
        $prefix = $stage !== ''
            ? $stage.($class !== '' ? ' / '.$class : '')
            : $class;

        return $prefix !== '' ? $prefix.' — '.$name : $name;
    }

    /**
     * @return array<int, array{subject_id: int, parent_section_id: int|null}>
     */
    private function normalizeLinkedTargets(Request $request, int $primarySubjectId): array
    {
        $targets = [];

        if ($request->has('linked_targets')) {
            foreach ((array) $request->input('linked_targets', []) as $row) {
                $subjectId = (int) ($row['subject_id'] ?? 0);
                if ($subjectId <= 0 || $subjectId === $primarySubjectId) {
                    continue;
                }

                $parentSectionId = isset($row['parent_section_id']) && $row['parent_section_id'] !== ''
                    ? (int) $row['parent_section_id']
                    : null;

                $targets[$subjectId] = [
                    'subject_id' => $subjectId,
                    'parent_section_id' => $parentSectionId > 0 ? $parentSectionId : null,
                ];
            }
        } else {
            foreach ((array) $request->input('linked_subject_ids', []) as $subjectId) {
                $subjectId = (int) $subjectId;
                if ($subjectId <= 0 || $subjectId === $primarySubjectId) {
                    continue;
                }

                $targets[$subjectId] = [
                    'subject_id' => $subjectId,
                    'parent_section_id' => null,
                ];
            }
        }

        return array_values($targets);
    }

    /**
     * ربط القسم بمواد إضافية (ظهوره في مواد أخرى).
     */
    public function linkSubjects(Request $request, SubjectSection $section)
    {
        $request->validate([
            'linked_targets' => ['nullable', 'array'],
            'linked_targets.*.subject_id' => ['required', 'integer', 'exists:subjects,id'],
            'linked_targets.*.parent_section_id' => ['nullable', 'integer', 'exists:subject_sections,id'],
            'linked_subject_ids' => ['nullable', 'array'],
            'linked_subject_ids.*' => ['integer', 'exists:subjects,id'],
        ]);

        $primarySubjectId = (int) $section->subject_id;
        $desiredTargets = $this->normalizeLinkedTargets($request, $primarySubjectId);

        $currentTargets = [];
        $mirrorRoots = SubjectSection::query()
            ->where('cloned_from_section_id', $section->id)
            ->get();

        foreach ($mirrorRoots as $mirrorRoot) {
            $currentTargets[(int) $mirrorRoot->subject_id] = [
                'subject_id' => (int) $mirrorRoot->subject_id,
                'parent_section_id' => $mirrorRoot->parent_id ? (int) $mirrorRoot->parent_id : null,
            ];
        }

        $legacySubjectIds = DB::table('section_subjects')
            ->where('section_id', $section->id)
            ->pluck('subject_id')
            ->map(fn ($id) => (int) $id)
            ->all();

        foreach ($legacySubjectIds as $legacySubjectId) {
            if (! isset($currentTargets[$legacySubjectId])) {
                $currentTargets[$legacySubjectId] = [
                    'subject_id' => $legacySubjectId,
                    'parent_section_id' => null,
                ];
            }
        }

        $desiredBySubject = collect($desiredTargets)->keyBy('subject_id');
        $currentBySubject = collect($currentTargets);

        foreach ($currentBySubject as $subjectId => $current) {
            if (! $desiredBySubject->has($subjectId)) {
                $targetSubject = Subject::find($subjectId);
                if ($targetSubject) {
                    $this->cloneService->removeMirrorForSubject($section, $targetSubject);
                }
            }
        }

        try {
            foreach ($desiredBySubject as $subjectId => $desired) {
                $targetSubject = Subject::find($subjectId);
                if (! $targetSubject) {
                    continue;
                }

                $current = $currentBySubject->get($subjectId);
                $desiredParentId = $desired['parent_section_id'] ?? null;
                $currentParentId = $current['parent_section_id'] ?? null;

                if ($current === null) {
                    $this->cloneService->cloneSectionTreeToSubject($section, $targetSubject, $desiredParentId);

                    continue;
                }

                if ((int) ($currentParentId ?? 0) !== (int) ($desiredParentId ?? 0)) {
                    $this->cloneService->removeMirrorForSubject($section, $targetSubject);
                    $this->cloneService->cloneSectionTreeToSubject($section, $targetSubject, $desiredParentId);
                }
            }
        } catch (\InvalidArgumentException $e) {
            return redirect()
                ->back()
                ->with('error', $e->getMessage());
        }

        DB::table('section_subjects')->where('section_id', $section->id)->delete();

        $linkedSubjectIds = $desiredBySubject->keys()->all();
        $linkedSubjects = Subject::with('schoolClass.stage')
            ->whereIn('id', $linkedSubjectIds)
            ->get();

        $count = $linkedSubjects->count();
        $labels = $linkedSubjects->map(fn ($s) => $this->formatLinkedSubjectBadgeText($s))->filter()->values()->toArray();

        $message = 'تم تحديث ربط القسم بالمواد بنجاح.';
        if ($count > 0) {
            $message .= ' تم إنشاء نسخة متزامنة في '.$count.' مادة';
            if (! empty($labels)) {
                $message .= ': '.implode('، ', array_slice($labels, 0, 5));
                if (count($labels) > 5) {
                    $message .= '...';
                }
            } else {
                $message .= '.';
            }
        } else {
            $message .= ' لا يوجد ربط لمواد إضافية حالياً.';
        }

        return redirect()
            ->back()
            ->with('success', $message);
    }

    /**
     * حذف قسم.
     */
    public function destroy(SubjectSection $section)
    {
        $subjectId = $section->subject_id;

        try {
            if ($section->isSyncMirror()) {
                $this->cloneService->deleteMirrorSubtree($section);
            } else {
                $this->cloneService->deleteCanonicalSubtree($section);
            }

            return redirect()
                ->route('admin.subjects.show', $subjectId)
                ->with('success', 'تم حذف القسم بنجاح.');
        } catch (\Exception $e) {
            return redirect()
                ->route('admin.subjects.show', $subjectId)
                ->with('error', 'حدث خطأ أثناء حذف القسم: ' . $e->getMessage());
        }
    }
}


