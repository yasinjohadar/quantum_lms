<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\StoreSubjectSectionRequest;
use App\Http\Requests\Admin\UpdateSubjectSectionRequest;
use App\Models\Subject;
use App\Models\SubjectSection;
use Illuminate\Support\Facades\Log;

class SubjectSectionController extends Controller
{
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

            // لو لم يُرسل ترتيب نضعه في آخر القائمة
            if (!isset($data['order']) || $data['order'] === null) {
                $maxOrder = $subject->sections()->max('order') ?? 0;
                $data['order'] = $maxOrder + 1;
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


