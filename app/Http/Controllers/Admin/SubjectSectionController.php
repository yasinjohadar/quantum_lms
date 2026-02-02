<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\StoreSubjectSectionRequest;
use App\Http\Requests\Admin\UpdateSubjectSectionRequest;
use App\Models\Subject;
use App\Models\SubjectSection;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class SubjectSectionController extends Controller
{
    public function __construct()
    {
        $this->middleware(['permission:subject-section-create'])->only('store');
        $this->middleware(['permission:subject-section-edit'])->only(['update', 'reorder']);
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

            // لو لم يُرسل ترتيب نضعه في آخر القائمة (بين الأخوة فقط)
            if (!isset($data['order']) || $data['order'] === null) {
                $maxOrder = SubjectSection::where('subject_id', $subject->id)
                    ->where('parent_id', $data['parent_id'])
                    ->max('order');
                $data['order'] = ($maxOrder ?? 0) + 1;
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
        $linkedSubjectIds = DB::table('section_subjects')
            ->where('section_id', $section->id)
            ->pluck('subject_id');
        $subjects = Subject::with('schoolClass.stage')
            ->whereIn('id', $linkedSubjectIds)
            ->get();
        $data = $subjects->map(function ($s) {
            return [
                'id' => $s->id,
                'name' => $s->name ?? '',
                'class_name' => optional($s->schoolClass)->name ?? '',
                'stage_name' => optional(optional($s->schoolClass)->stage)->name ?? '',
            ];
        })->values();
        return response()->json($data);
    }

    /**
     * ربط القسم بمواد إضافية (ظهوره في مواد أخرى).
     */
    public function linkSubjects(Request $request, SubjectSection $section)
    {
        $request->validate([
            'linked_subject_ids' => ['nullable', 'array'],
            'linked_subject_ids.*' => ['integer', 'exists:subjects,id'],
        ]);

        $linkedSubjectIds = $request->input('linked_subject_ids', []);
        $primarySubjectId = $section->subject_id;
        $linkedSubjectIds = array_values(array_unique(array_filter($linkedSubjectIds)));

        $existingLinkedIds = DB::table('section_subjects')
            ->where('section_id', $section->id)
            ->pluck('subject_id')
            ->toArray();
        $linkedSubjectIds = array_values(array_unique(array_merge($existingLinkedIds, $linkedSubjectIds)));
        $linkedSubjectIds = array_values(array_diff($linkedSubjectIds, [$primarySubjectId]));

        $section->linkedSubjects()->sync($linkedSubjectIds);

        $linkedSubjects = $section->linkedSubjects()->with('schoolClass.stage')->get();
        $count = $linkedSubjects->count();
        $labels = $linkedSubjects->map(function ($s) {
            return trim(collect([
                data_get($s, 'schoolClass.stage.name'),
                data_get($s, 'schoolClass.name'),
                $s->name,
            ])->filter()->implode(' — '));
        })->filter()->values()->toArray();

        $message = 'تم تحديث ربط القسم بالمواد بنجاح.';
        if ($count > 0) {
            $message .= ' القسم مربوط بـ ' . $count . ' مادة';
            if (!empty($labels)) {
                $message .= ': ' . implode('، ', array_slice($labels, 0, 5));
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
            $section->delete();

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


