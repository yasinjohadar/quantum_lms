<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\StoreUnitRequest;
use App\Http\Requests\Admin\UpdateUnitRequest;
use App\Models\SubjectSection;
use App\Models\Unit;
use App\Models\Question;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class UnitController extends Controller
{
    public function __construct()
    {
        $this->middleware(['permission:unit-create'])->only('store');
        $this->middleware(['permission:unit-edit'])->only(['update', 'reorder']);
        $this->middleware(['permission:unit-delete'])->only('destroy');
        $this->middleware(['permission:unit-questions'])->only('questions');
        $this->middleware(['permission:unit-attach-questions'])->only('attachQuestions');
        $this->middleware(['permission:unit-detach-question'])->only('detachQuestion');
        $this->middleware(['permission:unit-available-questions'])->only('availableQuestions');
    }

    /**
     * تخزين وحدة جديدة تابعة لقسم معيّن.
     */
    public function store(StoreUnitRequest $request, SubjectSection $section)
    {
        Log::info('محاولة إنشاء وحدة جديدة للقسم: ' . $section->id, $request->all());

        try {
            // التحقق من التخصيص
            $user = auth()->user();
            if ($user->hasRole('teacher') && !$user->hasAnyRole(['admin', 'supervisor'])) {
                $subject = $section->subject;
                if (!$user->isAssignedToSubject($subject->id) && 
                    !$user->isAssignedToClass($subject->class_id)) {
                    abort(403, 'غير مصرح لك بالوصول إلى هذه المادة');
                }
            }

            if ($section->hasChildSections()) {
                return redirect()
                    ->route('admin.subjects.show', $section->subject_id)
                    ->with('error', 'لا يمكن إضافة وحدات في قسم يملك أقساماً فرعية. أضف الوحدات في القسم الورقي (الأخير في التسلسل) فقط.');
            }

            $data = $request->validated();
            $data['section_id'] = $section->id;
            $data['is_active'] = $request->has('is_active');

            $parentId = $request->input('parent_id');
            if ($parentId) {
                $parent = Unit::find($parentId);
                if (!$parent || $parent->section_id != $section->id) {
                    return redirect()
                        ->route('admin.subjects.show', $section->subject_id)
                        ->with('error', 'الوحدة الأب يجب أن تنتمي لنفس القسم.');
                }
                $data['parent_id'] = $parentId;
            } else {
                $data['parent_id'] = null;
            }

            // لو لم يُرسل ترتيب نضعه في آخر القائمة (بين الأخوة فقط)
            if (!isset($data['order']) || $data['order'] === null) {
                $maxOrder = Unit::where('section_id', $section->id)
                    ->where('parent_id', $data['parent_id'])
                    ->max('order');
                $data['order'] = ($maxOrder ?? 0) + 1;
            }

            $unit = Unit::create($data);

            Log::info('تم إنشاء الوحدة بنجاح، ID: ' . $unit->id);

            return redirect()
                ->route('admin.subjects.show', $section->subject_id)
                ->with('success', 'تم إنشاء الوحدة "' . $unit->title . '" بنجاح.');
        } catch (\Exception $e) {
            Log::error('خطأ في إنشاء وحدة: ' . $e->getMessage());

            return redirect()
                ->route('admin.subjects.show', $section->subject_id)
                ->with('error', 'حدث خطأ أثناء إنشاء الوحدة: ' . $e->getMessage());
        }
    }

    /**
     * تحديث وحدة موجودة.
     */
    public function update(UpdateUnitRequest $request, Unit $unit)
    {
        try {
            $data = $request->validated();
            $data['is_active'] = $request->has('is_active');

            $parentId = $request->input('parent_id');
            if ($parentId !== null && $parentId !== '') {
                $parentId = (int) $parentId;
                if ($parentId === $unit->id) {
                    return redirect()
                        ->route('admin.subjects.show', $unit->section->subject_id)
                        ->with('error', 'لا يمكن جعل الوحدة أباً لنفسها.');
                }
                $parent = Unit::find($parentId);
                if (!$parent || $parent->section_id != $unit->section_id) {
                    return redirect()
                        ->route('admin.subjects.show', $unit->section->subject_id)
                        ->with('error', 'الوحدة الأب يجب أن تنتمي لنفس القسم.');
                }
                $unit->load('children');
                $descendantIds = $unit->getDescendantIds();
                if ($descendantIds->contains($parentId)) {
                    return redirect()
                        ->route('admin.subjects.show', $unit->section->subject_id)
                        ->with('error', 'لا يمكن جعل الوحدة أباً لأحد أحفادها (منع الحلقات).');
                }
                $data['parent_id'] = $parentId;
            } else {
                $data['parent_id'] = null;
            }

            $unit->update($data);

            return redirect()
                ->route('admin.subjects.show', $unit->section->subject_id)
                ->with('success', 'تم تحديث بيانات الوحدة بنجاح.');
        } catch (\Exception $e) {
            Log::error('خطأ في تحديث وحدة: ' . $e->getMessage());

            return redirect()
                ->route('admin.subjects.show', $unit->section->subject_id)
                ->with('error', 'حدث خطأ أثناء تحديث الوحدة: ' . $e->getMessage());
        }
    }

    /**
     * إعادة ترتيب الوحدات (جذر أو أبناء وحدة معيّنة ضمن القسم).
     */
    public function reorder(Request $request, SubjectSection $section)
    {
        $request->validate([
            'order' => ['required', 'array'],
            'order.*' => ['integer', 'exists:units,id'],
            'parent_id' => ['nullable', 'integer', 'exists:units,id'],
        ]);

        $order = $request->input('order');
        $parentId = $request->input('parent_id') ?: null;

        $units = Unit::where('section_id', $section->id)
            ->where('parent_id', $parentId)
            ->whereIn('id', $order)
            ->get();

        if ($units->count() !== count($order)) {
            return response()->json(['success' => false, 'message' => 'بعض الوحدات لا تنتمي لهذا السياق.'], 422);
        }

        foreach ($order as $index => $unitId) {
            Unit::where('id', $unitId)->update(['order' => $index]);
        }

        return response()->json(['success' => true]);
    }

    /**
     * حذف وحدة.
     */
    public function destroy(Unit $unit)
    {
        // التحقق من التخصيص
        $user = auth()->user();
        if ($user->hasRole('teacher') && !$user->hasAnyRole(['admin', 'supervisor'])) {
            $subject = $unit->section->subject;
            if (!$user->isAssignedToSubject($subject->id) && 
                !$user->isAssignedToClass($subject->class_id)) {
                abort(403, 'غير مصرح لك بالوصول إلى هذه الوحدة');
            }
        }
        
        $subjectId = $unit->section->subject_id;
        $unitTitle = $unit->title;

        try {
            $unit->delete();

            return redirect()
                ->route('admin.subjects.show', $subjectId)
                ->with('success', 'تم حذف الوحدة "' . $unitTitle . '" بنجاح.');
        } catch (\Exception $e) {
            Log::error('خطأ في حذف وحدة: ' . $e->getMessage());

            return redirect()
                ->route('admin.subjects.show', $subjectId)
                ->with('error', 'حدث خطأ أثناء حذف الوحدة: ' . $e->getMessage());
        }
    }

    /**
     * عرض أسئلة الوحدة
     */
    public function questions(Unit $unit)
    {
        $questions = $unit->questions()
            ->with('options')
            ->orderBy('created_at', 'desc')
            ->paginate(20);

        return view('admin.pages.units.questions', compact('unit', 'questions'));
    }

    /**
     * ربط أسئلة بالوحدة
     */
    public function attachQuestions(Request $request, Unit $unit)
    {
        $request->validate([
            'question_ids' => 'required|array',
            'question_ids.*' => 'exists:questions,id',
        ]);

        try {
            // ربط الأسئلة بالوحدة (مع تجنب التكرار)
            $unit->questions()->syncWithoutDetaching($request->question_ids);

            $count = count($request->question_ids);

            return redirect()
                ->route('admin.subjects.show', $unit->section->subject_id)
                ->with('success', "تم ربط {$count} سؤال بالوحدة بنجاح.");
        } catch (\Exception $e) {
            Log::error('خطأ في ربط الأسئلة بالوحدة: ' . $e->getMessage());

            return redirect()
                ->route('admin.subjects.show', $unit->section->subject_id)
                ->with('error', 'حدث خطأ أثناء ربط الأسئلة: ' . $e->getMessage());
        }
    }

    /**
     * فك ربط سؤال من الوحدة
     */
    public function detachQuestion(Unit $unit, Question $question)
    {
        try {
            $unit->questions()->detach($question->id);

            return redirect()
                ->route('admin.subjects.show', $unit->section->subject_id)
                ->with('success', 'تم فك ربط السؤال من الوحدة بنجاح.');
        } catch (\Exception $e) {
            Log::error('خطأ في فك ربط السؤال: ' . $e->getMessage());

            return redirect()
                ->route('admin.subjects.show', $unit->section->subject_id)
                ->with('error', 'حدث خطأ أثناء فك ربط السؤال: ' . $e->getMessage());
        }
    }

    /**
     * الأسئلة المتاحة للربط بالوحدة (للاستخدام في AJAX)
     */
    public function availableQuestions(Request $request, Unit $unit)
    {
        $query = Question::active()
            ->whereDoesntHave('units', function ($q) use ($unit) {
                $q->where('units.id', $unit->id);
            });

        // البحث
        if ($request->has('search') && $request->search) {
            $query->search($request->search);
        }

        // فلترة حسب النوع
        if ($request->has('type') && $request->type) {
            $query->ofType($request->type);
        }

        // فلترة حسب الصعوبة
        if ($request->has('difficulty') && $request->difficulty) {
            $query->ofDifficulty($request->difficulty);
        }

        $questions = $query->with('options')
            ->orderBy('created_at', 'desc')
            ->limit(50)
            ->get();

        return response()->json([
            'questions' => $questions->map(function ($q) {
                return [
                    'id' => $q->id,
                    'title' => $q->title,
                    'type' => $q->type,
                    'type_name' => $q->type_name,
                    'type_icon' => $q->type_icon,
                    'type_color' => $q->type_color,
                    'difficulty' => $q->difficulty,
                    'difficulty_name' => $q->difficulty_name,
                    'difficulty_color' => $q->difficulty_color,
                    'default_points' => $q->default_points,
                    'options_count' => $q->options->count(),
                ];
            }),
        ]);
    }
}

